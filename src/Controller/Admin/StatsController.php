<?php

namespace App\Controller\Admin;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/owner/visit-stats')]
#[IsGranted('ROLE_OWNER')]
class StatsController extends AbstractController
{
    #[Route('', name: 'admin_stats', methods: ['GET'])]
    public function index(Connection $connection): Response
    {
        $today = new \DateTimeImmutable('today');

        // Początek tygodnia: poniedziałek.
        $monday = $today->modify('monday this week');
        $nextMonday = $monday->modify('+7 days');

        // Wszystkie wejścia.
        $totalVisits = $connection->fetchOne(
            'SELECT COUNT(*) FROM visit'
        );

        // Unikalni odwiedzający.
        $uniqueVisitors = $connection->fetchOne(
            'SELECT COUNT(DISTINCT visitor_id) FROM visit'
        );

        // Wejścia dzisiaj.
        $todayVisits = $connection->fetchOne(
            'SELECT COUNT(*) FROM visit WHERE DATE(visited_at) = ?',
            [$today->format('Y-m-d')]
        );

        // Statystyki tygodniowe.
        $weeklyData = $connection->fetchAllKeyValue(
            'SELECT DATE(visited_at) AS day, COUNT(*) AS visits
             FROM visit
             WHERE visited_at >= ? AND visited_at < ?
             GROUP BY DATE(visited_at)
             ORDER BY day ASC',
            [
                $monday->format('Y-m-d 00:00:00'),
                $nextMonday->format('Y-m-d 00:00:00'),
            ]
        );

        // Uzupełniamy dni, w których nie było odwiedzin.
        $weeklyStats = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $monday->modify("+$i days");
            $dateKey = $date->format('Y-m-d');

            $weeklyStats[] = [
                'day' => $date->format('d.m'),
                'visits' => (int) ($weeklyData[$dateKey] ?? 0),
            ];
        }

        return $this->render('admin/stats/index.html.twig', [
            'totalVisits' => $totalVisits,
            'uniqueVisitors' => $uniqueVisitors,
            'todayVisits' => $todayVisits,
            'weeklyStats' => $weeklyStats,
        ]);
    }
}
