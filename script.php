<?php
// Интрефейс для тарифов
interface TariffInterface {
    public function calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute );
}

// Дополнительные услуги: GPS и водитель
trait GPS {
    public function addServiceGPS( int $hour, int $minute ) {
        if ( $hour < 1 ) {
            echo 'Ошибка: Аренда GPS возможна только от 1 часа';
            exit;
        }
        return $result = ceil( $hour . '.' . $minute ) * 15;
    }
}
trait Driver {
    public function addServiceDriver() : int {
        return 100;
    }
}

abstract class Tariff implements TariffInterface {
    use GPS;

    protected float $priceKilometer;
    protected float $priceMinute;

    protected function calculateTime( int $hour, int $minut ) {
        return $result = ( $hour * 60 ) + $minut;
    }
    protected function calculateDistance( int $kilometer, int $meter ) {
        return $result = $kilometer + ( $meter / 1000 );
    }
    protected function checkAge( int $age ) {
        if (( $age >= 18 ) && ( $age <= 21 )) {
            $this->priceKilometer += ( $this->priceKilometer * 10 ) / 100;
            $this->priceMinute += ( $this->priceMinute * 10 ) / 100;
        }
        if (( $age >= 18 ) && ( $age <= 65 )) {
            return true;
        }
        echo 'Ошибка: Возраст не подходит для аренды авто';
        exit;
    }

    abstract public function calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute );
}

class Basic extends Tariff {
    protected float $priceKilometer = 10;
    protected float $priceMinute = 3;

    public function calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute, bool $gps = false ) {
        if ( parent::checkAge( $age ) ) {
            $totalPrice =
                ( ( parent::calculateDistance( $kilometer, $meter ) ) * $this->priceKilometer )
            +
                ( ( parent::calculateTime( $hour, $minute ) ) * $this->priceMinute );

            if ( $gps ) {
                $totalPrice += $this->addServiceGPS( $hour, $minute );
            }

            echo 'К оплате: ' . round( $totalPrice, 2 ) . ' руб.';
        }
    }
}

class Hourly extends Tariff {
    use driver;

    protected float $priceKilometer = 0;
    protected float $priceMinute = 200;

    public function calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute, bool $gps = false, bool $driver = false ) {
        if ( parent::checkAge( $age ) ) {
            $totalPrice =
                ( ( parent::calculateDistance( $kilometer, $meter ) ) * $this->priceKilometer )
            +
                ( ( ceil( $hour . '.' . $minute ) ) * $this->priceMinute );

            if ( $gps ) {
                $totalPrice += $this->addServiceGPS( $hour, $minute );
            }
            if ( $driver ) {
                $totalPrice += $this->addServiceDriver();
            }

            echo 'К оплате: ' . round( $totalPrice, 2 ) . ' руб.';
        }
    }
}

class Daily extends Tariff {
    use driver;

    protected float $priceKilometer = 1;
    protected float $priceMinute = 1000;

    public function calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute, bool $gps = false, bool $driver = false ) {
        if ( parent::checkAge( $age ) ) {
            $quantityDays = ceil( $hour / 24 );

            $totalPrice =
                ( ( parent::calculateDistance( $kilometer, $meter ) ) * $this->priceKilometer )
            +
                ( ( $minute >= 30 ? $quantityDays += 1 : $quantityDays ) * $this->priceMinute );

            if ( $gps ) {
                $totalPrice += $this->addServiceGPS( $hour, $minute );
            }
            if ( $driver ) {
                $totalPrice += $this->addServiceDriver();
            }

            echo 'К оплате: ' . round( $totalPrice, 2 ) . ' руб.';
        }
    }
}

class Student extends Tariff {
    protected float $priceKilometer = 4;
    protected float $priceMinute = 1;

    public function calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute, bool $gps = false ) {
        if ( parent::checkAge( $age ) && $age > 25 ) {
            echo 'Ошибка: Для тарифа \'Студенческий\' возраст водителя не может больше 25 лет';
            exit;
        }
        $totalPrice =
            ( ( parent::calculateDistance( $kilometer, $meter ) ) * $this->priceKilometer )
        +
            ( ( parent::calculateTime( $hour, $minute ) ) * $this->priceMinute );

        if ( $gps ) {
            $totalPrice += $this->addServiceGPS( $hour, $minute );
        }
        echo 'К оплате: ' . round( $totalPrice, 2 ) . ' руб.';
    }
}

/*
    calculatingPrice( int $age, int $kilometer, int $meter, int $hour, int $minute, bool true/false, bool true/false ) -
        - метод для расчета поездки, где:
            $age - возраст водителя;
            $kilometer, $meter - проеханная дистанция (км, м);
            $hour, $minute - затраченное время на поездку (час, мин);
            true/false - добавление дополнительной услуги GPS;
            true/false - добавление дополнительной услуги Второй водитель ( Недоступно для Базового и Студенческого тарифов );
*/

$basic = new Basic();
$basic->calculatingPrice( 65, 18, 300, 1, 24, false );
echo '<br>';

$hourly = new Hourly();
$hourly->calculatingPrice( 19, 18, 300, 2, 25, true, true );
echo '<br>';

$daily = new Daily();
$daily->calculatingPrice( 26, 18, 300, 10, 22, false, true );
echo '<br>';

$student = new Student();
$student->calculatingPrice( 18, 18, 300, 1, 24, true );