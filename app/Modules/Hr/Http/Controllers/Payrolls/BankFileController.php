<?php

namespace App\Modules\Hr\Http\Controllers\Payrolls;

use App\Modules\Hr\Models\PayrollRun;
use QuickerFaster\UILibrary\Services\BankFiles\BankFileGeneratorFactory;
use Illuminate\Routing\Controller;

class BankFileController extends Controller
{
    public function download(PayrollRun $run)
    {

        // $run->paySchedule == null for multi-company mode
        if (!$run->paySchedule) {
            abort(400, 'Bank file generation is not supported for multi-company runs.');
        }

        $country = $run->paySchedule->country_code ?? 'US';
        $generator = BankFileGeneratorFactory::make($country);
        $content = $generator->generate($run);
        $fileName = $generator->getFileName($run);
        $mimeType = $generator->getMimeType();

        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $fileName, ['Content-Type' => $mimeType]);
    }
}
