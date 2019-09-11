<?php


namespace App\Form;

use DateTime;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ExchangeRatesModel
{
    private $startDate;

    /**
     * @Assert\Callback
     * @param ExecutionContextInterface $context
     * @throws Exception
     */
    public function validate(ExecutionContextInterface $context)
    {
        $actualDate = new DateTime();
        $startDate = $this->getStartDate();
        $days = $actualDate->diff($startDate)->format("%a");

        if ($actualDate <= $startDate) {
            $context->buildViolation('Podana data nie może być większa od aktualnej daty!')
                ->atPath('startDate')
                ->addViolation();
        } elseif ($days >= 367) {
            $context->buildViolation('Przekroczony limit 367 dni.')
                ->atPath('startDate')
                ->addViolation();
        }

    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }
}