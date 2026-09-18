<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\ClientMessage;
use App\Models\Client;

class ClientPortalComposer
{
    public function compose(View $view): void
    {
        $portalClientId = session('portal_client_id');
        $unreadMessagesCount = 0;
        $clientModel = null;

        if ($portalClientId) {
            $unreadMessagesCount = ClientMessage::query()
                ->where('client_id', $portalClientId)
                ->whereNotNull('user_id')
                ->whereNull('read_at')
                ->count();

            $clientModel = Client::find($portalClientId);
        }

        $view->with([
            'unreadMessagesCount' => $unreadMessagesCount,
            'clientModel' => $clientModel,
        ]);
    }
}
