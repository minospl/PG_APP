<?php

namespace App\Controller;

use App\Form\ExchangeRatesModel;
use App\Form\ExchangeRatesType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function index(Request $request)
    {
        $chartValues = null;
        $exchangeRates = null;
        $form = $this->createForm(ExchangeRatesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var ExchangeRatesModel $exchangeRatesModel */
            $exchangeRatesModel = $form->getData();

            $startDate = $exchangeRatesModel->getStartDate();
            $startDate = $startDate->format('Y-m-d');

            $exchangeRates = $this->getExchangeRates($startDate);
            $chartValues = $this->getChartValues($exchangeRates);
            $exchangeRates = $this->getDifferences($chartValues, $exchangeRates);

        }

        return $this->render('MainPage/index.html.twig', [
            'formType' => $form->createView(),
            'exchangeRates' => $exchangeRates,
            'chartValues' => $chartValues,
        ]);
    }

    /**
     * Wysyłanie zapytania
     *
     * @param $startDate
     * @return mixed|string
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function getExchangeRates($startDate)
    {
        $actualDate = new DateTime();
        $actualDate = $actualDate->format('Y-m-d');
        $client = HttpClient::create();
        $url = sprintf("http://api.nbp.pl/api/exchangerates/rates/c/usd/%s/%s/?format=json", $startDate, $actualDate);
        $response = $client->request('GET', $url);
        $exchangeRates = $response->getContent();
        $exchangeRates = json_decode($exchangeRates, true);
        return $exchangeRates;
    }

    /**
     * Dane do tabeli
     *
     * @param $exchangeRates
     * @return array
     */
    public function getChartValues($exchangeRates)
    {
        $chartAsk = array();
        $chartBid = array();
        $differenceArray = array();

        foreach ($exchangeRates['rates'] as $value) {
            $chartBid[] = array("y" => $value['bid'], "label" => $value['effectiveDate']);
            $chartAsk[] = array("y" => $value['ask'], "label" => $value['effectiveDate']);

            $summary = ($value['ask'] + $value['bid']) / 2;
            $differenceArray[] = $summary;
        }

        $chartBid = json_encode($chartBid, JSON_NUMERIC_CHECK);
        $chartAsk = json_encode($chartAsk, JSON_NUMERIC_CHECK);

        $chartSummary = array();
        $chartSummary['Bid'] = $chartBid;
        $chartSummary['Ask'] = $chartAsk;
        $chartSummary['differenceArray'] = $differenceArray;

        return $chartSummary;
    }

    /**
     * Różnice
     *
     * @param $chartValues
     * @param $exchangeRates
     * @return mixed
     */
    public function getDifferences($chartValues, $exchangeRates)
    {
        $differenceArray = $chartValues['differenceArray'];
        $count = count($differenceArray);
        $exchangeRates['rates'][0]['difference'] = '-';
        foreach ($differenceArray as $key => $value) {
            if ($count >= $key && $key > 0) {
                $difference = $differenceArray[$key - 1] - $differenceArray[$key];
                $exchangeRates['rates'][$key]['difference'] = $difference;
            }
        }
        return $exchangeRates;
    }
}