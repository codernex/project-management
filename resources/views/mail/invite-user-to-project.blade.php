<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation to Join the Team</title>
</head>
<body>
<table width="100%"
       style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px;">
    <tr>
        <td style="text-align: center;">
            <h2>{{ config('app.name') }} - Team Invitation</h2>
        </td>
    </tr>
    <tr>
        <td>
            <p>Hello,</p>

            <p>You've been invited to join the team on <strong>{{ config('app.name') }}</strong> by {{$inviterName }}
                .
            </p>

            <p>To accept the invitation, click the button below:</p>

            <p style="text-align: center;">
                <a href="{{ $invitationLink }}"
                   style="background-color: #28a745; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px;">
                    Accept Invitation
                </a>
            </p>

            <p>If you did not expect this email, you can safely ignore it.</p>

            <p>Thank you,</p>
            <p>The {{ config('app.name') }} Team</p>
        </td>
    </tr>
    <tr>
        <td style="text-align: center; font-size: 12px; color: #aaa; padding-top: 20px;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </td>
    </tr>
</table>
</body>
</html>
