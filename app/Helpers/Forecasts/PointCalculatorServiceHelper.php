<?php

namespace App\Helpers\Forecasts;

use App\Helpers\InstanceTrait;
class PointCalculatorServiceHelper extends ForecastAbstractionHelper
{
    use InstanceTrait;

    protected array $resultIds = [];
    protected array $userGivenIds = [];

    public function calculate(array $resultIds = [], array $userGivenIds = []): self
    {
        $this->resultIds = $resultIds;
        $this->userGivenIds = $userGivenIds;
        return $this;
    }

    public function foundGoldPlace(): bool
    {
        return $this->resultIds[0] == $this->userGivenIds[0];
    }

    public function foundSilverPlace(): bool
    {
        return $this->resultIds[1] == $this->userGivenIds[1];
    }

    public function foundBronzePlace(): bool
    {
        return $this->resultIds[2] == $this->userGivenIds[2];
    }

    public function found4Place(): bool
    {
        return $this->resultIds[3] == $this->userGivenIds[3];
    }

    public function found5Place(): bool
    {
        return $this->resultIds[4] == $this->userGivenIds[4];
    }

    public function found6Place(): bool
    {
        return $this->resultIds[5] == $this->userGivenIds[5];
    }

    public  function foundBonus1Perfection(): bool
    {
        return $this->foundGoldPlace() && $this->foundSilverPlace() && $this->foundBronzePlace();
    }

    public function foundBonus2None(): bool
    {
        return $this->getFirstThreeIndirectFinding() === 0;
    }

    public function foundBonus2One(): bool
    {
        return $this->getFirstThreeIndirectFinding() === 1;
    }

    public function foundBonus2Pair(): bool
    {
        return $this->getFirstThreeIndirectFinding() === 2;
    }

    public function foundBonus2All(): bool
    {
        return $this->getFirstThreeIndirectFinding() === 3;
    }

    public function foundBonus3None(): bool
    {
        return $this->getLastThreeIndirectFinding() === 0;
    }

    public function foundBonus3One(): bool
    {
        return $this->getLastThreeIndirectFinding() === 1;
    }

    public function foundBonus3Pair(): bool
    {
        return $this->getLastThreeIndirectFinding() === 2;
    }

    public function foundBonus3All(): bool
    {
        return $this->getLastThreeIndirectFinding() === 3;
    }

    private function getFirstThreeIndirectFinding(): int
    {
        $counter = 0;
        $counter += intval(in_array($this->resultIds[0], $userFirstThreePlaces = $this->getUserFirstThreePlaces()) && !$this->foundGoldPlace());
        $counter += intval(in_array($this->resultIds[1], $userFirstThreePlaces) && !$this->foundSilverPlace());
        $counter += intval(in_array($this->resultIds[2], $userFirstThreePlaces) && !$this->foundBronzePlace());
        return $counter;
    }

    private function getLastThreeIndirectFinding(): int
    {
        $counter = 0;
        $counter += intval(in_array($this->resultIds[3], $userFirstThreePlaces = $this->getUserLastThreePlaces())  && $this->resultIds[3] != $this->userGivenIds[3]);
        $counter += intval(in_array($this->resultIds[4], $userFirstThreePlaces)  && $this->resultIds[4] != $this->userGivenIds[4]);
        $counter += intval(in_array($this->resultIds[5], $userFirstThreePlaces)  && $this->resultIds[5] != $this->userGivenIds[5]);
        return $counter;
    }

    private function getUserFirstThreePlaces(): array
    {
        return array_slice($this->userGivenIds, 0, 3);
    }

    private function getUserLastThreePlaces(): array
    {
        return array_slice($this->userGivenIds, 3, 3);
    }
}
