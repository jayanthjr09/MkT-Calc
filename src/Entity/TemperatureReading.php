<?php

namespace App\Entity;

use App\Repository\TemperatureReadingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TemperatureReadingRepository::class)
 * @ORM\Table(name="temperature_reading")
 */
class TemperatureReading
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;

    /**
     * @ORM\Column(type="float")
     */
    private $temperature;

    /**
     * @ORM\ManyToOne(targetEntity=DataSet::class, inversedBy="temperatureReadings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $dataSet;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getDataSet(): ?DataSet
    {
        return $this->dataSet;
    }

    public function setDataSet(?DataSet $dataSet): self
    {
        $this->dataSet = $dataSet;

        return $this;
    }
}