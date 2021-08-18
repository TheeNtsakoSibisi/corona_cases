<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Component\HttpClient\HttpClient;

class ChartController extends AbstractController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchGitHubInformation(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.covid19api.com/total/country/south-africa'
        );

        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

        echo $content;
    }

    /**
     * @Route("/chart", name="chart")
     */
    public function index(ChartBuilderInterface $builder): Response
    {
        $client = HttpClient::create();

        $url = 'https://api.covid19api.com/total/country/south-africa';
        $response = $client->request('GET', $url);
        $versions = $response->toArray();
        
        $chartData = json_encode($versions, true);
        $decodedChartData = json_decode($chartData, true);
        //echo $decodedChartData[240]['Date'];
        
        for($i = 0, $size = count($decodedChartData); $i < $size; ++$i) {

        $chart = $builder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => [$decodedChartData[$i]['Date']], //['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            'datasets' => [
                [
                    'label' => 'Confirmed',
                    'backgroundColor' => 'rgb(0, 0, 255)',
                    'borderColor' => 'rgb(0, 0, 255)',
                    'data' => [$decodedChartData[$i]['Confirmed']], //[0, 10, 5, 2, 20, 30, 25, 0, 10, 5, 2, 20],
                ],
                [
                    'label' => 'Deaths',
                    'backgroundColor' => 'rgb(255, 0, 0)',
                    'borderColor' => 'rgb(255, 0, 0)',
                    'data' => [$decodedChartData[$i]['Deaths']], //[16, 10, 5, 2, 10, 20, 35, 5, 2, 10, 20, 35],
                ],
                [
                    'label' => 'Recovered',
                    'backgroundColor' => 'rgb(0, 128, 0)',
                    'borderColor' => 'rgb(0, 128, 0)',
                    'data' => [$decodedChartData[$i]['Recovered']], //[12, 20, 9, 6, 7, 2, 15, 9, 6, 7, 2, 15],
                ],
                [
                    'label' => 'Active',
                    'backgroundColor' => 'rgb(255, 255, 0)',
                    'borderColor' => 'rgb(255, 255, 0)',
                    'data' => [$decodedChartData[$i]['Active']], //[5, 15, 2, 7, 15, 10, 5, 2, 7, 15, 10, 5],
                ],
            ],
        ]);}

        return $this->render('chart/index.html.twig', [
            'chart' => $chart,
        ]);
    }
}
