<?php

namespace App\Services;

class ElectronicSignatureService
{
    /**
     * Bangun properti eksplisit terisolasi untuk item Paraf Elektronik
     */
    public function buildParafItemProps(
        string $roleLabel,
        string $pejabatNama,
        string $pejabatNip,
        bool $isSigned,
        ?string $signedAt = null,
        ?string $verifyUrl = null,
        string $parafCode = 'kapokja',
        ?string $qrDataUri = null,
        string $waitingLabel = '[ Menunggu Verifikasi & Paraf Tata Naskah ]'
    ): array {
        $resolvedQr = $qrDataUri;
        if ($isSigned && empty($resolvedQr) && !empty($verifyUrl)) {
            $separator = str_contains($verifyUrl, '?') ? '&' : '?';
            $resolvedQr = QrCodeService::generateDataUri(
                $verifyUrl . $separator . 'paraf=' . urlencode($parafCode) . '&nip=' . urlencode($pejabatNip)
            );
        }

        return [
            'roleLabel'    => $roleLabel,
            'pejabatNama'  => str_replace(['M.kep', 'Sp.kep', 'Sp. Kep.M.B'], ['M.Kep', 'Sp.Kep', 'Sp.Kep.M.B'], $pejabatNama),
            'pejabatNip'   => $pejabatNip,
            'isSigned'     => $isSigned,
            'signedAt'     => $signedAt,
            'qrDataUri'    => $resolvedQr,
            'waitingLabel' => $waitingLabel,
        ];
    }

    /**
     * Bangun properti eksplisit terisolasi untuk blok Tanda Tangan Elektronik (TTE)
     */
    public function buildTteProps(
        string $jabatan,
        string $pejabatNama,
        string $pejabatNip,
        bool $isSigned,
        ?string $signedAt = null,
        ?string $verifyUrl = null,
        string $tteCode = 'pybmc',
        ?string $qrDataUri = null,
        ?string $mandatPrefix = null,
        string $align = 'left'
    ): array {
        $resolvedQr = $qrDataUri;
        if ($isSigned && empty($resolvedQr) && !empty($verifyUrl)) {
            $separator = str_contains($verifyUrl, '?') ? '&' : '?';
            $resolvedQr = QrCodeService::generateDataUri(
                $verifyUrl . $separator . 'tte=' . urlencode($tteCode) . '&nip=' . urlencode($pejabatNip)
            );
        }

        if ($mandatPrefix === null && str_contains(strtolower($jabatan), 'wakil dekan')) {
            $mandatPrefix = 'a.n. Dekan Fakultas Keperawatan';
        }

        return [
            'mandatPrefix' => $mandatPrefix,
            'jabatan'      => $jabatan,
            'pejabatNama'  => str_replace(['M.kep', 'Sp.kep', 'Sp. Kep.M.B'], ['M.Kep', 'Sp.Kep', 'Sp.Kep.M.B'], $pejabatNama),
            'pejabatNip'   => $pejabatNip,
            'isSigned'     => $isSigned,
            'signedAt'     => $signedAt,
            'qrDataUri'    => $resolvedQr,
            'align'        => $align,
        ];
    }
}
