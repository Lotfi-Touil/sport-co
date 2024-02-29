<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class PDFExportService
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function exportReportToPDF($report): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(true);
        // Ajout pour améliorer le support des styles CSS
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $html = $this->twig->render('back/report/pdf_report_template.html.twig', [
            'report' => $report
        ]);

        // link to tailwind css file to improve the rendering of the PDF
        $html .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.16/dist/tailwind.min.css">';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
