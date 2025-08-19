<?php

namespace NumaxLab\Lunar\Geslib\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NumaxLab\Lunar\Geslib\Admin\Filament\Pages\GeslibDashboardPage;

// Added for Filament URL

class GeslibConfigurationError extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $configKey;

    protected string $details;

    /**
     * Create a new notification instance.
     *
     * @param  string  $configKey  The configuration key that has an issue.
     * @param  string  $details  A message detailing the configuration issue.
     */
    public function __construct(string $configKey, string $details)
    {
        $this->configKey = $configKey;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $dashboardUrl = null;
        if (class_exists(GeslibDashboardPage::class) && method_exists(GeslibDashboardPage::class, 'getUrl')) {
            try {
                $dashboardUrl = GeslibDashboardPage::getUrl();
            } catch (\Exception $e) {
                // In case URL generation fails
                $dashboardUrl = null;
            }
        }

        $mailMessage = (new MailMessage)
            ->subject('Geslib Configuration Error Detected')
            ->greeting('Geslib Configuration Error')
            ->line('A configuration issue has been detected for the Lunar-Geslib integration.')
            ->line('Configuration Key: '.$this->configKey)
            ->line('Details: '.$this->details)
            ->line('Timestamp: '.now()->toDateTimeString());

        if ($dashboardUrl) {
            $mailMessage->action('Go to Geslib Dashboard', $dashboardUrl);
        } else {
            $mailMessage->line(
                'Please check the Geslib Dashboard in your Filament admin panel for more details and to review your settings.',
            );
        }

        $mailMessage->line(
            'Resolving this configuration issue is important for the correct functioning of the Geslib integration.',
        );

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'config_key' => $this->configKey,
            'details' => $this->details,
        ];
    }
}
