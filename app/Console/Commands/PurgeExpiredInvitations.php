<?php

namespace App\Console\Commands;

use App\Models\CompanyInvitation;
use Illuminate\Console\Command;

class PurgeExpiredInvitations extends Command
{
    protected $signature = 'invitations:purge-expired {--days=30 : Delete unaccepted invitations expired for more than this many days}';

    protected $description = 'Delete stale unaccepted company invitations (accepted rows are kept as audit trail)';

    public function handle(): int
    {
        $cutoff = now()->subDays((int) $this->option('days'));

        $deleted = CompanyInvitation::whereNull('accepted_at')
            ->where('expires_at', '<', $cutoff)
            ->delete();

        $this->info("Purged {$deleted} expired invitation(s).");

        return self::SUCCESS;
    }
}
