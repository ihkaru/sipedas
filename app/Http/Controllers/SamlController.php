<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OneLogin\Saml2\Auth as SamlAuth;
use OneLogin\Saml2\Settings as SamlSettings;

class SamlController extends Controller
{
    /**
     * Inisialisasi instance OneLogin SAML Auth dengan config aplikasi.
     */
    protected function getSamlAuth(): SamlAuth
    {
        $settings = config('saml');
        return new SamlAuth($settings);
    }

    /**
     * Memulai proses login SAML SP-Initiated (Redirect ke rIDM SSO).
     */
    public function login(Request $request)
    {
        $auth = $this->getSamlAuth();
        $relayState = $request->input('return_to', url('/a'));
        
        $loginUrl = $auth->login($relayState, [], false, false, true);
        return redirect()->away($loginUrl);
    }

    /**
     * Assertion Consumer Service (ACS): Memproses respons SAML POST dari rIDM.
     */
    public function acs(Request $request)
    {
        try {
            $auth = $this->getSamlAuth();
            $auth->processResponse();

            $errors = $auth->getErrors();
            if (!empty($errors)) {
                $reason = $auth->getLastErrorReason();
                Log::error('SAML ACS Verification Error: ' . implode(', ', $errors), [
                    'reason' => $reason,
                ]);
                return redirect('/a/login')->withErrors([
                    'saml' => 'Otentikasi SAML gagal diverifikasi: ' . $reason,
                ]);
            }

            if (!$auth->isAuthenticated()) {
                return redirect('/a/login')->withErrors([
                    'saml' => 'SAML Assertion tidak valid atau pengguna belum diautentikasi.',
                ]);
            }

            // Ekstrak NameID dan Atribut dari SAML Assertion
            $nameId = $auth->getNameId();
            $attributes = $auth->getAttributes();

            $email = $attributes['email'][0] ?? $nameId;
            $nip = $attributes['nip'][0] ?? null;

            // Cari user terdaftar di database SIPEDAS
            $user = User::where('email', $email)->first();
            if (!$user && $nip) {
                $user = User::whereHas('pegawai', fn($q) => $q->where('nip', $nip))->first();
            }

            if (!$user) {
                Log::warning('SAML User Not Found in SIPEDAS', [
                    'nameId' => $nameId,
                    'email' => $email,
                    'attributes' => $attributes,
                ]);
                return redirect('/a/login')->withErrors([
                    'saml' => "Akun pegawai ({$email}) belum terdaftar di SIPEDAS. Silakan hubungi admin.",
                ]);
            }

            // Login user ke sesi Laravel
            Auth::login($user, remember: true);
            $request->session()->regenerate();
            $request->session()->put('saml_name_id', $nameId);
            $request->session()->put('saml_session_index', $auth->getSessionIndex());
            $request->session()->put('saml_name_id_format', $auth->getNameIdFormat());

            // Handle RelayState jika ada
            $relayState = $request->input('RelayState');
            if (!empty($relayState) && !str_contains($relayState, '/saml/')) {
                return redirect()->to($relayState);
            }

            return redirect()->intended('/a');
        } catch (\Throwable $e) {
            Log::error('SAML ACS Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect('/a/login')->withErrors([
                'saml' => 'Terjadi kesalahan saat memproses login SSO SAML: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Endpoint Metadata SP XML untuk didaftarkan di rIDM.
     */
    public function metadata()
    {
        try {
            $settings = new SamlSettings(config('saml'), true);
            $metadata = $settings->getSPMetadata();
            $errors = $settings->validateMetadata($metadata);

            if (!empty($errors)) {
                return response(implode(', ', $errors), 500);
            }

            return response($metadata, 200, ['Content-Type' => 'text/xml']);
        } catch (\Throwable $e) {
            return response('Error generating SAML SP metadata: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Memulai proses Single Logout (SLO) ke rIDM.
     */
    public function logout(Request $request)
    {
        try {
            $nameId = $request->session()->get('saml_name_id');
            $sessionIndex = $request->session()->get('saml_session_index');
            $nameIdFormat = $request->session()->get('saml_name_id_format');

            $auth = $this->getSamlAuth();

            // Bersihkan sesi lokal Laravel
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $returnTo = url('/a/login?logged_out=1');
            $logoutUrl = $auth->logout($returnTo, [], $nameId, $sessionIndex, true, $nameIdFormat);
            
            if (!empty($logoutUrl)) {
                return redirect()->away($logoutUrl);
            }
        } catch (\Throwable $e) {
            Log::warning('SAML Logout Exception: ' . $e->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/a/login?logged_out=1');
    }

    /**
     * Single Logout Service (SLS).
     */
    public function sls(Request $request)
    {
        try {
            $auth = $this->getSamlAuth();
            $auth->processSLO();
        } catch (\Throwable $e) {
            Log::warning('SAML SLS Exception: ' . $e->getMessage());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $relayState = $request->input('RelayState');
        if (!empty($relayState) && !str_contains($relayState, '/saml/')) {
            return redirect()->to($relayState);
        }

        return redirect('/a/login?logged_out=1');
    }
}
