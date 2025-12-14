<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Evaluasi;

class UpdateEvaluasiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $evaluasi = $this->route('evaluasi');
        
        // Admin can always update
        if ($this->user()->isAdmin()) {
            return true;
        }

        // Kaprodi can update their prodi's evaluasi if status allows
        if ($this->user()->isKaprodi()) {
            return $this->user()->prodi_id === $evaluasi->prodi_id && $evaluasi->canEdit();
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'renstra_id' => ['sometimes', 'required', 'exists:renstra,id'],
            'prodi_id' => ['sometimes', 'required', 'exists:prodi,id'],
            'target_id' => ['nullable', 'exists:renstra_target,id'],
            'semester' => ['sometimes', 'required', 'in:' . Evaluasi::SEMESTER_GANJIL . ',' . Evaluasi::SEMESTER_GENAP],
            'tahun_evaluasi' => ['sometimes', 'required', 'integer', 'min:2020', 'max:' . (date('Y') + 5)],
            'realisasi' => ['sometimes', 'required', 'numeric', 'min:0'],
            'ketercapaian' => ['sometimes', 'required', 'numeric', 'min:0', 'max:200'],
            'bukti_id' => ['nullable', 'exists:evaluasi_bukti,id'],
        ];

        $ketercapaian = $this->input('ketercapaian', 0);

        // Conditional validation based on achievement
        if ($ketercapaian < 100) {
            // NOT ACHIEVED - require root cause analysis
            $rules['akar_masalah'] = ['required', 'string', 'min:10', 'max:5000'];
            $rules['faktor_penghambat'] = ['required', 'string', 'min:10', 'max:5000'];
            $rules['faktor_pendukung'] = ['nullable', 'string', 'max:5000'];
        } else {
            // ACHIEVED - require supporting factors
            $rules['faktor_pendukung'] = ['required', 'string', 'min:10', 'max:5000'];
            $rules['akar_masalah'] = ['nullable', 'string', 'max:5000'];
            $rules['faktor_penghambat'] = ['nullable', 'string', 'max:5000'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'renstra_id.required' => 'RENSTRA harus dipilih',
            'renstra_id.exists' => 'RENSTRA tidak valid',
            'prodi_id.required' => 'Program Studi harus dipilih',
            'prodi_id.exists' => 'Program Studi tidak valid',
            'semester.required' => 'Semester harus dipilih',
            'semester.in' => 'Semester harus Ganjil atau Genap',
            'tahun_evaluasi.required' => 'Tahun evaluasi harus diisi',
            'tahun_evaluasi.integer' => 'Tahun evaluasi harus berupa angka',
            'tahun_evaluasi.min' => 'Tahun evaluasi minimal 2020',
            'realisasi.required' => 'Realisasi harus diisi',
            'realisasi.numeric' => 'Realisasi harus berupa angka',
            'realisasi.min' => 'Realisasi tidak boleh negatif',
            'ketercapaian.required' => 'Ketercapaian harus diisi',
            'ketercapaian.numeric' => 'Ketercapaian harus berupa angka',
            'ketercapaian.min' => 'Ketercapaian tidak boleh negatif',
            'ketercapaian.max' => 'Ketercapaian maksimal 200%',
            
            // Conditional messages for NOT ACHIEVED
            'akar_masalah.required' => 'Akar masalah wajib diisi karena target belum tercapai (< 100%)',
            'akar_masalah.min' => 'Akar masalah minimal 10 karakter',
            'akar_masalah.max' => 'Akar masalah maksimal 5000 karakter',
            'faktor_penghambat.required' => 'Faktor penghambat wajib diisi karena target belum tercapai (< 100%)',
            'faktor_penghambat.min' => 'Faktor penghambat minimal 10 karakter',
            'faktor_penghambat.max' => 'Faktor penghambat maksimal 5000 karakter',
            
            // Conditional messages for ACHIEVED
            'faktor_pendukung.required' => 'Faktor pendukung wajib diisi karena target sudah tercapai (≥ 100%)',
            'faktor_pendukung.min' => 'Faktor pendukung minimal 10 karakter',
            'faktor_pendukung.max' => 'Faktor pendukung maksimal 5000 karakter',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'renstra_id' => 'RENSTRA',
            'prodi_id' => 'Program Studi',
            'target_id' => 'Target',
            'semester' => 'semester',
            'tahun_evaluasi' => 'tahun evaluasi',
            'realisasi' => 'realisasi',
            'ketercapaian' => 'ketercapaian',
            'akar_masalah' => 'akar masalah',
            'faktor_pendukung' => 'faktor pendukung',
            'faktor_penghambat' => 'faktor penghambat',
            'bukti_id' => 'bukti evaluasi',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure ketercapaian is available for conditional validation
        if ($this->has('realisasi') && $this->has('target_value')) {
            $target = (float) $this->input('target_value', 1);
            $realisasi = (float) $this->input('realisasi', 0);
            
            if ($target > 0) {
                $this->merge([
                    'ketercapaian' => ($realisasi / $target) * 100
                ]);
            }
        }
    }
}
