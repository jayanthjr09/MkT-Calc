<?php

namespace App\Controller;

use App\Entity\DataSet;
use App\Entity\TemperatureReading;
use App\Form\DataSetType;
use App\Service\MktCalculator;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class DataSetController extends AbstractController
{
    private $logger;
    private $mktCalculator;

    public function __construct(LoggerInterface $logger, MktCalculator $mktCalculator)
    {
        $this->logger = $logger;
        $this->mktCalculator = $mktCalculator;
    }

    /**
     * @Route("/", name="upload")
     */
    public function upload(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $this->logger->info('Upload action called');
        $allowedExtensions = ['csv', 'txt'];

        $dataSets = $entityManager->getRepository(DataSet::class)->findBy([], ['createdAt' => 'DESC']);

        $dataSet = new DataSet();
        $form = $this->createForm(DataSetType::class, $dataSet);
        $form->handleRequest($request);

        $this->logger->info('Form submission status', ['submitted' => $form->isSubmitted()]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('Form is valid');

            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            if ($file) {
                if (!in_array(strtolower($file->guessExtension()), $allowedExtensions)) {
                    $this->addFlash('error', 'Error: Invalid file format. Please upload a CSV file.');
                    return $this->redirectToRoute('upload');
                }
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads',
                        $newFilename
                    );

                    $this->logger->info('File uploaded successfully', ['filename' => $newFilename]);

                    // Parse the CSV file and insert data into the database
                    $filePath = $this->getParameter('kernel.project_dir').'/public/uploads/'.$newFilename;
                    if (($handle = fopen($filePath, 'r')) !== false) {
                        // Skip the header row
                        fgetcsv($handle, 1000, ',');

                        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                            $this->logger->info('Processing row', ['row' => $data]);

                            try {
                                $timestamp = (new \DateTime())->setTimestamp((int)$data[0]);
                                $temperature = floatval($data[1]);

                                $temperatureReading = new TemperatureReading();
                                $temperatureReading->setTimestamp($timestamp);
                                $temperatureReading->setTemperature($temperature);
                                $temperatureReading->setDataSet($dataSet);

                                $entityManager->persist($temperatureReading);
                            } catch (\Exception $e) {
                                $this->logger->error('Error processing row', [
                                    'row' => $data,
                                    'error' => $e->getMessage(),
                                ]);
                                $this->addFlash('error', 'Invalid data to process ' . $e->getMessage());
                                return $this->redirectToRoute('upload');
                            }
                        }
                        fclose($handle);

                        $dataSet->setName($originalFilename);
                        $dataSet->setCreatedAt(new \DateTime());
                        $entityManager->persist($dataSet);
                        $entityManager->flush();

                        $this->logger->info('Data inserted successfully');
                        $this->addFlash('success', 'File uploaded and data processed successfully.');

                        // return $this->redirectToRoute('show_readings', ['id' => $dataSet->getId()]);
                        return $this->redirectToRoute('upload');
                    } else {
                        $this->logger->error('Failed to open file for reading');
                        $this->addFlash('error', 'Failed to open file for reading.');
                        return $this->redirectToRoute('upload');
                    }
                } catch (FileException $e) {
                    $this->logger->error('Failed to upload file', ['exception' => $e]);
                    $this->addFlash('error', 'Failed to upload file: ' . $e->getMessage());
                    return $this->redirectToRoute('upload');
                }
            }
            return $this->redirectToRoute('upload');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Form submission is invalid.');
        }

        return $this->render('upload.html.twig', [
            'form' => $form->createView(),
            'dataSets' => $dataSets,
        ]);
    }

    /**
     * @Route("/readings/{id}", name="show_readings")
     */
    public function showReadings(int $id, EntityManagerInterface $entityManager): Response
    {
        $dataSet = $entityManager->getRepository(DataSet::class)->find($id);

        if (!$dataSet) {
            throw $this->createNotFoundException('The dataset does not exist');
        }

        $mkt = $this->mktCalculator->calculate($dataSet);

        return $this->render('readings.html.twig', [
            'dataSet' => $dataSet,
            'temperatureReadings' => $dataSet->getTemperatureReadings(),
            'mkt' => $mkt,
        ]);
    }

    /**
     * @Route("/latest-readings", name="latest_readings")
     */
    public function latestReadings(EntityManagerInterface $entityManager): Response
    {
        $latestDataSet = $entityManager->getRepository(DataSet::class)->findOneBy([], ['createdAt' => 'DESC']);

        if (!$latestDataSet) {
            throw $this->createNotFoundException('No datasets found');
        }

        return $this->redirectToRoute('show_readings', ['id' => $latestDataSet->getId()]);
    }

    /**
     * @Route("/custom-mkt", name="custom_mkt")
     */
    public function customMkt(Request $request, EntityManagerInterface $entityManager): Response
    {
        $startDateTime = $request->query->get('start_datetime');
        $endDateTime = $request->query->get('end_datetime');
        $dataSets = $entityManager->getRepository(DataSet::class)->findAll();

        if ($startDateTime && $endDateTime) {
            $startDateTime = new \DateTime($startDateTime);
            $endDateTime = new \DateTime($endDateTime);

            $readings = $entityManager->getRepository(TemperatureReading::class)
                ->createQueryBuilder('tr')
                ->where('tr.timestamp >= :start')
                ->andWhere('tr.timestamp <= :end')
                ->setParameter('start', $startDateTime)
                ->setParameter('end', $endDateTime)
                ->getQuery()
                ->getResult();

            if (count($readings) === 0) {
                $this->addFlash('error', 'No readings found for the specified period.');
                return $this->render('custom_mkt.html.twig', [
                    'dataSets' => $dataSets,
                ]);
            }

            $dataSet = new DataSet();
            foreach ($readings as $reading) {
                $dataSet->addTemperatureReading($reading);
            }

            $mkt = $this->mktCalculator->calculate($dataSet);

            return $this->render('custom_mkt.html.twig', [
                'dataSets' => $dataSets,
                'temperatureReadings' => $readings,
                'mkt' => $mkt,
                'startDateTime' => $startDateTime,
                'endDateTime' => $endDateTime,
            ]);
        }

        return $this->render('custom_mkt.html.twig', [
            'dataSets' => $dataSets,
        ]);
    }
}