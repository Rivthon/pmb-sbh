<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\Supplier;
use Carbon\CarbonPeriod;
use App\Models\WawancaraPmb;
use App\Models\PmbOfflineQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Helpers\Utilities\DateHelper;
use App\Helpers\Utilities\RandomGenerator;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil data wawancara (jika ada)
        $wawancara = WawancaraPmb::where('calon_mahasiswa_id', $user->id)->first();
        $latestQueue = $this->latestQueueForUser($user);

        return view('dashboard.pages.dashboard.index', compact('wawancara', 'latestQueue'));
    }

    public function selection(Request $request)
    {
        $user = auth()->user();
        $latestQueue = $this->latestQueueForUser($user);
        $hasQueue = (bool) $latestQueue;
        $isOnlineSelection = $hasQueue && $latestQueue->isOnlineSelection();

        return view('dashboard.pages.dashboard.selection', compact('user', 'latestQueue', 'hasQueue', 'isOnlineSelection'));
    }

    private function latestQueueForUser(User $user): ?PmbOfflineQueue
    {
        return PmbOfflineQueue::with(['session', 'room', 'stageSteps.stage', 'stageSteps.room', 'stageSteps.officer'])
            ->where('user_id', $user->id)
            ->latest()
            ->latest('id')
            ->first();
    }
}
