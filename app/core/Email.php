<?php

class Email
{
    private static $from = 'noreply@e-store.local';
    
    /**
     * Send email notification for order status change
     */
    public static function sendOrderStatusNotification($userEmail, $userName, $orderId, $oldStatus, $newStatus)
    {
        $subject = "Order #$orderId Status Updated - E-Store";
        
        $statusLabels = [
            'pending' => 'Pending',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered'
        ];
        
        $statusMessages = [
            'pending' => 'Your order is being processed.',
            'shipped' => 'Your order has been shipped and is on its way!',
            'delivered' => 'Your order has been delivered. Thank you for your purchase!'
        ];
        
        $body = self::getEmailTemplate(
            $userName,
            "Order #$orderId Status Updated",
            "Your order status has changed from <strong>{$statusLabels[$oldStatus]}</strong> to <strong>{$statusLabels[$newStatus]}</strong>.",
            $statusMessages[$newStatus] ?? '',
            "View Order",
            getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . "/order-tracking.php?order_id=$orderId" : "http://localhost/projet_php/e-store/order-tracking.php?order_id=$orderId"
        );
        
        return self::send($userEmail, $subject, $body);
    }
    
    /**
     * Send confirmation email for new order
     */
    public static function sendOrderConfirmation($userEmail, $userName, $orderId, $orderTotal)
    {
        $subject = "Order Confirmation #$orderId - E-Store";
        
        $body = self::getEmailTemplate(
            $userName,
            "Order Confirmation",
            "Thank you for your order! We've received your purchase.",
            "Order Total: <strong>\$$orderTotal</strong><br>Order ID: <strong>#$orderId</strong>",
            "Track Your Order",
            getenv('APP_URL') ? rtrim(getenv('APP_URL'), '/') . "/order-tracking.php?order_id=$orderId" : "http://localhost/projet_php/e-store/order-tracking.php?order_id=$orderId"
        );
        
        return self::send($userEmail, $subject, $body);
    }
    
    /**
     * Generic email template
     */
    private static function getEmailTemplate($name, $title, $message, $details, $buttonText, $buttonUrl)
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; border-bottom: 3px solid #007bff; }
                .content { padding: 20px; }
                .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
                .button { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .details { background: #f0f0f0; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin: 0; color: #007bff;'>📦 E-Store</h1>
                </div>
                <div class='content'>
                    <h2>$title</h2>
                    <p>Hi $name,</p>
                    <p>$message</p>
                    <div class='details'>
                        $details
                    </div>
                    <p>
                        <a href='$buttonUrl' class='button'>$buttonText</a>
                    </p>
                    <p>
                        If you have any questions, please don't hesitate to contact us.
                    </p>
                    <p>
                        Best regards,<br>
                        <strong>E-Store Team</strong>
                    </p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " E-Store. All rights reserved.</p>
                    <p>This is an automated email. Please do not reply.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Send email using PHP mail()
     */
    private static function send($to, $subject, $body)
    {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . self::$from . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        return mail($to, $subject, $body, $headers);
    }
}
