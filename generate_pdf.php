<?php
require_once 'vendor/autoload.php';
include('config.php');
requireAdminAuth();

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_GET['start_date']) && isset($_GET['end_date']) && isset($_GET['report_type'])) {
    $start_date = $connect->real_escape_string($_GET['start_date']);
    $end_date = $connect->real_escape_string($_GET['end_date']);
    $report_type = $connect->real_escape_string($_GET['report_type']);
    
    // Options for PDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica');
    
    $dompdf = new Dompdf($options);
    
    // Generate HTML content for PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>e-RecycloTrack Report</title>
        <style>
            body { font-family: Helvetica, Arial, sans-serif; font-size: 12pt; }
            h1 { color: #1E3A8A; text-align: center; }
            h2 { color: #16B981; border-bottom: 2px solid #16B981; padding-bottom: 5px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #000; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .header { text-align: center; margin-bottom: 30px; }
            .footer { margin-top: 30px; font-size: 10pt; color: #666; text-align: center; }
            .summary { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .logo { text-align: center; margin-bottom: 20px; }
            .page-break { page-break-before: always; }
        </style>
    </head>
    <body>
        <div class="logo">
            <h1>e-RecycloTrack</h1>
        </div>
        
        <div class="header">
            <h2>'.ucfirst($report_type).' Report</h2>
            <p>Date Range: '.$start_date.' to '.$end_date.'</p>
            <p>Generated on: '.date('Y-m-d H:i:s').'</p>
        </div>';
    
    // Add report data
    if ($report_type === 'sales') 
	{
        $sql = "SELECT rt.transaction_date, c.customer_name, c.customer_ic, 
                       GROUP_CONCAT(CONCAT(ti.weight, 'kg ', it.type_name) SEPARATOR ', ') as items,
                       SUM(ti.subtotal) as total_amount, SUM(ti.points_earned) as total_points
                FROM recycling_transactions rt
                JOIN customer c ON rt.customer_id = c.customer_id
                JOIN transaction_items ti ON rt.transaction_id = ti.transaction_id
                JOIN item_types it ON ti.type_id = it.type_id
                WHERE rt.transaction_date BETWEEN '$start_date' AND '$end_date'
                GROUP BY rt.transaction_id
                ORDER BY rt.transaction_date DESC";
        
        // Get summary data
        $summary_sql = "SELECT COUNT(DISTINCT rt.transaction_id) as total_transactions,
                               COUNT(DISTINCT c.customer_id) as total_customers,
                               SUM(ti.subtotal) as total_revenue,
                               SUM(ti.points_earned) as total_points
                        FROM recycling_transactions rt
                        JOIN customer c ON rt.customer_id = c.customer_id
                        JOIN transaction_items ti ON rt.transaction_id = ti.transaction_id
                        WHERE rt.transaction_date BETWEEN '$start_date' AND '$end_date'";
    } 
	else		
	{
        $sql = "SELECT c.customer_name, c.customer_ic, c.customer_phone, c.customer_points,
                       COUNT(rt.transaction_id) as total_transactions,
                       SUM(rt.total_amount) as total_spent,
                       MAX(rt.transaction_date) as last_transaction
                FROM customer c
                LEFT JOIN recycling_transactions rt ON c.customer_id = rt.customer_id
                WHERE (rt.transaction_date BETWEEN '$start_date' AND '$end_date' OR rt.transaction_date IS NULL)
                GROUP BY c.customer_id
                ORDER BY total_spent DESC";
        
        // Get summary data
        $summary_sql = "SELECT COUNT(*) as total_customers,
                               SUM(customer_points) as total_points,
                               COUNT(rt.transaction_id) as total_transactions,
                               SUM(rt.total_amount) as total_revenue
                        FROM customer c
                        LEFT JOIN recycling_transactions rt ON c.customer_id = rt.customer_id
                        AND rt.transaction_date BETWEEN '$start_date' AND '$end_date'";
    }
    
    // Get summary data
    $summary_result = $connect->query($summary_sql);
    $summary = $summary_result->fetch_assoc();
    
    $html .= '<div class="summary">';
    $html .= '<h3>Summary</h3>';
    foreach ($summary as $key => $value) 
	{
        $html .= '<p><strong>'.ucwords(str_replace('_', ' ', $key)).':</strong> '.$value.'</p>';
    }
    $html .= '</div>';
    
    // Get detailed data
    $result = $connect->query($sql);
    
    if ($result->num_rows > 0) 
	{
        $html .= '<table>';
        $html .= '<tr>';
        // Table headers
        while ($field = $result->fetch_field()) 
		{
            $html .= '<th>'.ucwords(str_replace('_', ' ', $field->name)).'</th>';
        }
        $html .= '</tr>';
        
        // Table data
        while ($row = $result->fetch_assoc()) 
		{
            $html .= '<tr>';
            foreach ($row as $value) 
			{
                $html .= '<td>'.$value.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
    } 
	else 
	{
        $html .= '<p>No data found for the selected criteria.</p>';
    }
    
    $html .= '
        <div class="footer">
            <p>Generated by e-RecycloTrack System</p>
            <p>© 2025 e-RecycloTrack. All rights reserved.</p>
        </div>
    </body>
    </html>';
    
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    
    // Output the generated PDF
    $dompdf->stream('recyclotrack_report_'.date('Ymd_His').'.pdf', 
	[
        'Attachment' => true
    ]);
    
    exit();
} 
else 
{
    $_SESSION['message'] = 'Invalid parameters for PDF generation';
    header('Location: admin.php');
    exit();
}
?>