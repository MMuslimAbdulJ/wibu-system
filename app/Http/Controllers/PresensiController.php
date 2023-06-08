<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    public function create()
    {
        $hariIni = date('Y-m-d');
        $nim = Auth::guard('mahasiswa')->user()->nim;
        $cek = DB::table('presensi')->where('tgl_presensi', $hariIni)->where('nim', $nim)->count();
        return view('presensi.create', compact('cek'));
    }

    public function store(Request $request)
    {
        // $latitudeKampus = -6.837034802074808;
        // $longitudeKampus = 107.18866841042329;

        $latitudeKampus = -6.836980232645298;
        $longitudeKampus = 107.18892648470208;

        $nim = Auth::guard('mahasiswa')->user()->nim;
        $tgl_presensi = date('Y-m-d');
        $jam = date("H:i:s");
        $lokasi = $request->lokasi;

        $lokasiUser = explode(',', $lokasi);
        $latitudeUser = $lokasiUser[0];
        $longitudeUser = $lokasiUser[1];

        $jarak = $this->distance($latitudeKampus, $longitudeKampus, $latitudeUser, $longitudeUser);
        $radius = round($jarak['meters']);

        $image = $request->image;
        $folderPath = 'public/uploads/absensi';
        $formatName = $nim . '-' . $tgl_presensi;
        $image_parts = explode(';base64', $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . '.png';
        $file = $folderPath . $fileName;

        $cek = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nim', $nim)->count();
        if ($radius > 10) {
            echo "error|Maaf, presensi anda diluar radius. Jarak anda adalah $radius meter dari kelas|";
        } else {
            if ($cek > 0) {
                $data_pulang = [
                    'jam_out' => $jam,
                    'foto_out' => $fileName,
                    'lokasi_out' => $lokasi,
                ];
                $update = DB::table('presensi')->where('tgl_presensi', $tgl_presensi)->where('nim', $nim)->update($data_pulang);
                if ($update) {
                    echo "success|Terima Kasih, presensi pulang anda sudah terekam|out";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Presensi gagal terekam|out";
                }
            } else {
                $data = [
                    'nim' => $nim,
                    'tgl_presensi' => $tgl_presensi,
                    'jam_in' => $jam,
                    'foto_in' => $fileName,
                    'lokasi_in' => $lokasi,
                ];
                $simpan = DB::table('presensi')->insert($data);
                if ($simpan) {
                    echo "success|Terima Kasih, Presensi anda sudah terekam|in";
                    Storage::put($file, $image_base64);
                } else {
                    echo "error|Presensi gagal terekam|in";
                }
            }
        }
        //Menghitung Jarak
    }
    private function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }
}
