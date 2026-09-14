namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRiwayatPendidikanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pegawai_id'    => ['required', 'exists:pegawai,id'],
            'tingkat'       => ['required', 'string'],
            'institusi'     => ['required', 'string', 'max:255'],
            'fakultas'      => ['nullable', 'string', 'max:255'],
            'jurusan'       => ['nullable', 'string', 'max:255'],
            'tahun_lulus'   => ['nullable', 'numeric', 'digits:4'],
            'gelar_depan'   => ['nullable', 'string', 'max:50'],
            'gelar_belakang'=> ['nullable', 'string', 'max:50'],
            'nomor_ijazah'  => ['nullable', 'string', 'max:255'],
        ];
    }
}