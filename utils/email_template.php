<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $subject; ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .email-header {
            background-color: #13e43dff; /* Dark Blue */
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 100px;
            height: auto;
        }
        .email-header h1 {
            margin: 10px 0 0;
            font-size: 24px;
        }
        .email-body {
            padding: 30px;
            line-height: 1.6;
        }
        .email-body h2 {
            color: #004080;
            font-size: 20px;
        }
        .email-footer {
            background-color: #f4f4f4;
            color: #666666;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid #dddddd;
        }
        .email-footer p {
            margin: 0;
        }
        .button, .button:visited {
            display: inline-block;
            padding: 0.5rem 2rem;
            margin: 20px 0;
            background-color: #00b33fff; /* A slightly lighter blue */
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://jhcsc.edu.ph/wp-content/uploads/2024/12/jhcsclogo-transparent-1000x1000.png" alt="JHCSC Logo">
            <h1>JHCSC DSA Student Council</h1>
        </div>
        <div class="email-body">
            <h2><?php echo $subject; ?></h2>
            <?php echo $bodyContent; ?>
            <div style="text-align: center;">
                <a href="http://localhost/student_affairs/Login/login.html" class="button">Go to Website</a>
            </div>
            <p>If you have any questions, please don't hesitate to contact the DSA office.</p>
            <p>Thank you,</p>
            <p><strong>The JHCSC Student Council Team</strong></p>
        </div>
        <div class="email-footer">
            <p>&copy; <?php echo date("Y"); ?> JHC Student Council. All rights reserved.</p>
            <p>JH Cerilles State College, Pagadian Campus, Pagadian, Zamboanga del Sur</p>
        </div>
    </div>
</body>
</html>