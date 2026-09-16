<?php

namespace App\Http\Controllers\LandingPage;

use Illuminate\Http\Request;
use App\Models\CustomerMessage;
use App\Models\BiayaKuliah;
use App\Models\LandingVideo;
use App\Models\HeroFeature;
use App\Models\LandingInfoCard;
use App\Models\LandingMedia;
use App\Http\Controllers\Controller;
use App\Http\Requests\LandingPage\SendCustomerMessageRequest;

class MainController extends Controller
{
    public function home()
    {
        $biayaData = BiayaKuliah::getForLandingPage();
        $landingVideos = LandingVideo::active()->get();
        $heroFeatures = HeroFeature::active()->get();
        $landingInfoCards = LandingInfoCard::active()->get()->groupBy('section');
        $landingMedia = LandingMedia::query()->get()->keyBy('key');

        return view('landing-page.index', compact('biayaData', 'landingVideos', 'heroFeatures', 'landingInfoCards', 'landingMedia'));
    }

    public function sendCustomerMessage(SendCustomerMessageRequest $request)
    {
        $requestDTO = $request->validated();
        try {
            $requestDTO['customer_ip'] = $request->getClientIp();
            CustomerMessage::create($requestDTO);

            return redirect(route('home'))->with('toastSuccess', 'Terima kasih sudah menghubungi kami, tim kami akan segera menghubungi anda');
        } catch (\Throwable $th) {
            return redirect(route('home'))->with('toastSuccess', 'Terjadi kesalahan saat mengirim pesan');
        }
    }
}
