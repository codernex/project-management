<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Task Assigned</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333333;
        }

        p {
            line-height: 1.6;
            color: #555555;
        }

        .button {
            display: inline-block;
            padding: 10px 15px;
            margin: 20px 0;
            background-color: #007BFF;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>New Task Assigned</h1>
    <p>Hi {{ $user->name }},</p>
    <p>You have been assigned a new task: <strong>{{ $task->title }}</strong>.</p>
    <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('F j, Y') }}</p>
    <p>You can view the task and manage it by clicking the button below:</p>
    <a href="{{'http://localhost:8000/projects/'. $task->project_id}}" class="button">View Task</a>
    <p>Thank you!</p>
    <p>Best Regards,<br>Your Project Management Team</p>
</div>
</body>
</html>
