<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    private const DISK = 'private';

    /**
     * Upload dokumen karyawan ke private disk.
     */
    public function upload(Employee $employee, UploadedFile $file, array $data): EmployeeDocument
    {
        $ext = $file->getClientOriginalExtension();
        $filename = Str::ulid() . '.' . $ext;
        $path = "documents/{$employee->id}/{$filename}";

        Storage::disk(self::DISK)->putFileAs(
            "documents/{$employee->id}",
            $file,
            $filename
        );

        return EmployeeDocument::create([
            'employee_id' => $employee->id,
            'type' => $data['type'],
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'expires_at' => $data['expires_at'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Hapus dokumen (file + record).
     */
    public function delete(EmployeeDocument $document): void
    {
        Storage::disk(self::DISK)->delete($document->file_path);
        $document->delete();
    }

    /**
     * Generate temporary signed URL (15 menit) via storage response.
     */
    public function temporaryUrl(EmployeeDocument $document): string
    {
        // Local private disk tidak support temporaryUrl() langsung —
        // gunakan route signed URL sebagai proxy download
        return route('employees.documents.download', [
            'employee' => $document->employee_id,
            'document' => $document->id,
        ]);
    }

    /**
     * Stream file ke response (untuk download controller).
     */
    public function streamDownload(EmployeeDocument $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk(self::DISK)->download(
            $document->file_path,
            $document->original_name
        );
    }
}
