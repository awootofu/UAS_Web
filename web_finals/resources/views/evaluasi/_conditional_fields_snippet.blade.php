{{-- 
    Example Blade View Snippet for Evaluasi Form with Conditional Fields
    
    This snippet demonstrates how to show/hide fields based on ketercapaian (achievement).
    
    Logic:
    - If ketercapaian >= 100%: Show faktor_pendukung (required), hide akar_masalah & faktor_penghambat
    - If ketercapaian < 100%: Show akar_masalah & faktor_penghambat (required), hide faktor_pendukung
    
    Add this to your evaluasi create/edit forms.
--}}

<!-- Realisasi Input -->
<div class="mb-4">
    <label for="realisasi" class="block text-sm font-medium text-gray-700">
        Realisasi <span class="text-red-500">*</span>
    </label>
    <input type="number" 
           step="0.01" 
           name="realisasi" 
           id="realisasi"
           value="{{ old('realisasi', $evaluasi->realisasi ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
           required
           oninput="calculateKetercapaian()">
    @error('realisasi')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<!-- Ketercapaian Input (Auto-calculated or manual) -->
<div class="mb-4">
    <label for="ketercapaian" class="block text-sm font-medium text-gray-700">
        Ketercapaian (%) <span class="text-red-500">*</span>
    </label>
    <input type="number" 
           step="0.01" 
           name="ketercapaian" 
           id="ketercapaian"
           value="{{ old('ketercapaian', $evaluasi->ketercapaian ?? '') }}"
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
           required
           oninput="toggleConditionalFields()">
    @error('ketercapaian')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    <p class="mt-1 text-sm text-gray-500">
        Target tercapai jika ≥ 100%
    </p>
</div>

<!-- Achievement Status Indicator -->
<div id="achievement-status" class="mb-4 p-3 rounded-md hidden">
    <div id="achieved-badge" class="hidden">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
            ✓ Target Tercapai
        </span>
    </div>
    <div id="not-achieved-badge" class="hidden">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
            ✗ Target Belum Tercapai
        </span>
    </div>
</div>

<!-- CONDITIONAL SECTION: Target NOT ACHIEVED (< 100%) -->
<div id="not-achieved-section" class="hidden">
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
        <h4 class="text-md font-semibold text-red-800 mb-3">
            📋 Analisis Target Belum Tercapai (Wajib Diisi)
        </h4>
        
        <!-- Akar Masalah -->
        <div class="mb-4">
            <label for="akar_masalah" class="block text-sm font-medium text-gray-700">
                Akar Masalah <span class="text-red-500">*</span>
            </label>
            <textarea name="akar_masalah" 
                      id="akar_masalah"
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Jelaskan akar masalah mengapa target belum tercapai...">{{ old('akar_masalah', $evaluasi->akar_masalah ?? '') }}</textarea>
            @error('akar_masalah')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">
                Minimal 10 karakter. Jelaskan penyebab utama tidak tercapainya target.
            </p>
        </div>

        <!-- Faktor Penghambat -->
        <div class="mb-4">
            <label for="faktor_penghambat" class="block text-sm font-medium text-gray-700">
                Faktor Penghambat <span class="text-red-500">*</span>
            </label>
            <textarea name="faktor_penghambat" 
                      id="faktor_penghambat"
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Jelaskan faktor-faktor yang menghambat pencapaian target...">{{ old('faktor_penghambat', $evaluasi->faktor_penghambat ?? '') }}</textarea>
            @error('faktor_penghambat')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">
                Minimal 10 karakter. Jelaskan kendala atau hambatan yang dihadapi.
            </p>
        </div>
    </div>
</div>

<!-- CONDITIONAL SECTION: Target ACHIEVED (>= 100%) -->
<div id="achieved-section" class="hidden">
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
        <h4 class="text-md font-semibold text-green-800 mb-3">
            ✓ Analisis Target Tercapai (Wajib Diisi)
        </h4>
        
        <!-- Faktor Pendukung -->
        <div class="mb-4">
            <label for="faktor_pendukung" class="block text-sm font-medium text-gray-700">
                Faktor Pendukung <span class="text-red-500">*</span>
            </label>
            <textarea name="faktor_pendukung" 
                      id="faktor_pendukung"
                      rows="4"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Jelaskan faktor-faktor yang mendukung tercapainya target...">{{ old('faktor_pendukung', $evaluasi->faktor_pendukung ?? '') }}</textarea>
            @error('faktor_pendukung')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-sm text-gray-500">
                Minimal 10 karakter. Jelaskan strategi atau faktor sukses yang berkontribusi.
            </p>
        </div>

        <!-- Optional: Akar Masalah (jika ada yang perlu diperbaiki meski target tercapai) -->
        <div class="mb-4">
            <label for="akar_masalah_optional" class="block text-sm font-medium text-gray-700">
                Catatan Perbaikan (Opsional)
            </label>
            <textarea name="akar_masalah" 
                      id="akar_masalah_optional"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                      placeholder="Jika ada hal yang perlu diperbaiki meski target tercapai...">{{ old('akar_masalah', $evaluasi->akar_masalah ?? '') }}</textarea>
            <p class="mt-1 text-sm text-gray-500">
                Opsional. Catatan untuk perbaikan berkelanjutan.
            </p>
        </div>
    </div>
</div>

{{-- JavaScript for Conditional Display --}}
<script>
    // Calculate ketercapaian based on realisasi and target
    function calculateKetercapaian() {
        const realisasi = parseFloat(document.getElementById('realisasi').value) || 0;
        const target = parseFloat(document.getElementById('target_value')?.value) || 1;
        
        if (target > 0) {
            const ketercapaian = (realisasi / target) * 100;
            document.getElementById('ketercapaian').value = ketercapaian.toFixed(2);
        }
        
        toggleConditionalFields();
    }

    // Toggle conditional fields based on ketercapaian
    function toggleConditionalFields() {
        const ketercapaian = parseFloat(document.getElementById('ketercapaian').value) || 0;
        
        const achievedSection = document.getElementById('achieved-section');
        const notAchievedSection = document.getElementById('not-achieved-section');
        const achievedBadge = document.getElementById('achieved-badge');
        const notAchievedBadge = document.getElementById('not-achieved-badge');
        const achievementStatus = document.getElementById('achievement-status');

        // Show achievement status
        achievementStatus.classList.remove('hidden');

        if (ketercapaian >= 100) {
            // ACHIEVED: Show supporting factors section
            achievedSection.classList.remove('hidden');
            notAchievedSection.classList.add('hidden');
            achievedBadge.classList.remove('hidden');
            notAchievedBadge.classList.add('hidden');
            
            // Set required attributes
            document.getElementById('faktor_pendukung').setAttribute('required', 'required');
            document.getElementById('akar_masalah').removeAttribute('required');
            document.getElementById('faktor_penghambat').removeAttribute('required');
        } else {
            // NOT ACHIEVED: Show root cause analysis section
            notAchievedSection.classList.remove('hidden');
            achievedSection.classList.add('hidden');
            notAchievedBadge.classList.remove('hidden');
            achievedBadge.classList.add('hidden');
            
            // Set required attributes
            document.getElementById('akar_masalah').setAttribute('required', 'required');
            document.getElementById('faktor_penghambat').setAttribute('required', 'required');
            document.getElementById('faktor_pendukung').removeAttribute('required');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleConditionalFields();
        
        // Add event listener to ketercapaian input
        const ketercapaianInput = document.getElementById('ketercapaian');
        if (ketercapaianInput) {
            ketercapaianInput.addEventListener('input', toggleConditionalFields);
        }

        // Add event listener to realisasi input
        const realisasiInput = document.getElementById('realisasi');
        if (realisasiInput) {
            realisasiInput.addEventListener('input', calculateKetercapaian);
        }
    });
</script>

{{-- CSS for smooth transitions --}}
<style>
    #achieved-section,
    #not-achieved-section {
        transition: all 0.3s ease-in-out;
    }
</style>
