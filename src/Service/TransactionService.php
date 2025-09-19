<?php

namespace App\Service;

use App\Entity\Scs\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

/**
 * Class BookingFormService
 * @package App\Service
 */
class TransactionService
{
    /**
	 * @param EntityManagerInterface $entityManager
     * @param Environment $twig
	 */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Environment $twig,
    )
    {
    }

    /**
     * Génération détail PDF
     * 
     * @param Transaction $transaction
     * @return void
     */
    public function generatePdf($transactions)
    {
        $pdfOptions = new \Dompdf\Options();
        $pdfOptions->set('defaultFont', 'Arial')->setIsRemoteEnabled(true);
        $pdfOptions->set("isPhpEnabled", true);
        $pdf = new Dompdf($pdfOptions);
        
        $template = $this->twig->render("transaction/pdf.html.twig", [
            'transaction' => $transactions
        ]);

        $pdf->loadHtml($template);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $date           = (new \DateTime())->format('d_m_Y_H_i_s');
        $filename       = "transaction" . $date . ".pdf";
        $pdf->stream($filename, ["Attachment" => true]);
        exit();
    }
}