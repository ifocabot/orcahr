<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmployeeDocumentController extends Controller
{
    public function __construct(private DocumentService $service)
    {
    }

    /** Upload dokumen baru untuk karyawan */
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $data = $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120', // 5MB
            'type' => 'required|in:ktp,npwp,kontrak,ijazah,sertifikasi,foto,other',
            'expires_at' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->service->upload($employee, $request->file('file'), $data);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Dokumen berhasil diupload.');
    }

    /** Download dokumen (stream dari private disk) */
    public function download(Employee $employee, EmployeeDocument $document)
    {
        $this->authorize('view', $employee);

        abort_if($document->employee_id !== $employee->id, 404);

        return $this->service->streamDownload($document);
    }

    /** Hapus dokumen */
    public function destroy(Employee $employee, EmployeeDocument $document): RedirectResponse
    {
        $this->authorize('update', $employee);

        abort_if($document->employee_id !== $employee->id, 404);

        $this->service->delete($document);

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
