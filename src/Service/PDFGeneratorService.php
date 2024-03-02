<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGeneratorService
{
    public function generatePDF(string $html): string
    {
        // Configurez Dompdf selon vos besoins
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Créez une instance de Dompdf avec les options configurées
        $dompdf = new Dompdf($pdfOptions);

        // Chargez le HTML
        $dompdf->loadHtml($html);

        // (Optionnel) Configurez la taille du papier et l'orientation
        $dompdf->setPaper('A4', 'portrait');

        // Rendrez le PDF
        $dompdf->render();

        // Sortie du PDF généré
        return $dompdf->output();
    }
}
