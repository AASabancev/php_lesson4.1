<?php

echo "<pre>";

interface iService
{
    public function calcService($minutes);
}

class GPSService implements iService
{
    public string $title = 'GPS';
    private $costMinute = 15 / 60; // лень высчитывать:)
    private $minMinutes = 60;

    public function calcService($minutes): int
    {
        /**
         * минимум 60 минут, но округляем в большую сторону до часа.
         */
        $minutes = ceil(max($minutes, $this->minMinutes) / 60) * 60;
        return $minutes * $this->costMinute;
    }
}

class DriverService implements iService
{
    public string $title = 'Дополнительный водитель';
    private $minCost = 100;

    public function calcService($minutes): int
    {
        return $this->minCost;
    }
}

interface iTariff
{
    function calculate(int $minutes, int $kilometers);

    function addService(iService $iService);
}

abstract class Tariff implements iTariff
{
    protected int $priceKm;
    protected float $priceMinute;
    protected array $services;
    protected float $cost = 0;
    protected string $title;

    function addService(iService $iService)
    {
        $this->services[] = $iService;
    }

    public function calculate(int $minutes, int $kilometers)
    {
        $addTitle = [];
        $minutes && $addTitle[] = $minutes . 'мин.';
        $kilometers && $addTitle[] = $kilometers . 'км.';

        echo "Тариф " . $this->title . '(' . join(', ', $addTitle) . ')';

        $desc = [
            'text' => [],
            'costs' => [],
        ];

        /* @var iService $service */
        foreach ($this->services as $service) {
            $this->cost += $cost = $service->calcService($minutes);
            $desc['text'][] = $service->title . ' ' . $cost . 'руб';
            $desc['costs'][] = $cost;
            echo PHP_EOL . "- " . $service->title;
        }


        if ($kilometers && $this->priceKm) {
            $this->cost += $cost = ($this->priceKm * $kilometers);
            $desc['text'][] = $kilometers . 'км*' . round($this->priceKm, 2) . 'руб/км';
            $desc['costs'][] = $cost;
        }

        if ($minutes && $this->priceMinute) {
            $this->cost += $cost = ($this->priceMinute * $minutes);
            $desc['text'][] = $minutes . 'мин*' . round($this->priceMinute, 2) . 'руб/мин';
            $desc['costs'][] = $cost;
        }
        echo PHP_EOL . PHP_EOL
            . '= ' . join(' + ', $desc['text'])
            . ' = ' . join(' + ', $desc['costs'])
            . ' = ' . $this->cost;
        return $this->cost;
    }
}


class TariffBase extends Tariff
{
    protected string $title = 'базовый';
    protected int $priceKm = 10;
    protected float $priceMinute = 3;
}

class TariffHourly extends Tariff
{
    protected string $title = 'почасовой';
    protected int $priceKm = 0;
    protected float $priceMinute = 200 / 60; // ну лень высчитывать:)

    public function calculate(int $minutes, int $kilometers)
    {
        /**
         * Округляем до целого часа в бОльшую сторону
         */
        $minutes = ceil($minutes / 60) * 60;
        return parent::calculate($minutes, $kilometers);
    }

}

class TariffStudent extends Tariff
{
    protected string $title = 'студенческий';
    protected int $priceKm = 4;
    protected float $priceMinute = 1;
}

$gpsService = new GPSService();
$driverService = new DriverService();


$tariffBase = new TariffBase();
$tariffBase->addService($gpsService);
$tariffBase->calculate(61, 10);

echo PHP_EOL . PHP_EOL;

$tariffBase = new TariffHourly();
$tariffBase->addService($gpsService);
$tariffBase->addService($driverService);
$tariffBase->calculate(97, 35);

echo PHP_EOL . PHP_EOL;

$tariffBase = new TariffStudent();
$tariffBase->addService($gpsService);
$tariffBase->addService($driverService);
$tariffBase->calculate(42, 7);

