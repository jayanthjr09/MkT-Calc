<?php

namespace App\Entity;

use App\Repository\DataSetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass=DataSetRepository::class)
 * @ORM\Table(name="data_set")
 */
class DataSet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=TemperatureReading::class, mappedBy="dataSet", cascade={"persist", "remove"})
     */
    private $temperatureReadings;

    /**
     * @var File|null
     */
    private $file;

    public function __construct()
    {
        $this->temperatureReadings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|TemperatureReading[]
     */
    public function getTemperatureReadings(): Collection
    {
        return $this->temperatureReadings;
    }

    public function addTemperatureReading(TemperatureReading $temperatureReading): self
    {
        if (!$this->temperatureReadings->contains($temperatureReading)) {
            $this->temperatureReadings[] = $temperatureReading;
            $temperatureReading->setDataSet($this);
        }

        return $this;
    }

    public function removeTemperatureReading(TemperatureReading $temperatureReading): self
    {
        if ($this->temperatureReadings->removeElement($temperatureReading)) {
            // set the owning side to null (unless already changed)
            if ($temperatureReading->getDataSet() === $this) {
                $temperatureReading->setDataSet(null);
            }
        }

        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }
}