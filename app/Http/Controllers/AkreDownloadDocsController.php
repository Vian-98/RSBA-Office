<?php

namespace App\Http\Controllers;

use App\Models\Akreditasi\AkreBabElement;
use App\Models\Akreditasi\AkreChapter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Akreditasi\AkreDocuments;
use App\Models\Akreditasi\AkreElement;
use ZipArchive;

class AkreDownloadDocsController extends Controller
{
    public function downloadFile($documentId)
    {
        $document = AkreDocuments::findOrFail($documentId);

        if (!Storage::exists($document->path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::download($document->path, $document->filename);
    }

    // Download all files from an element (as ZIP if multiple)
    public function downloadElement($elementId)
    {
        $element = AkreElement::with('documents')->findOrFail($elementId);
        $documents = $element->documents;

        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found'], 404);
        }

        // If only one document, download directly
        if ($documents->count() === 1) {
            return $this->downloadFile($documents->first()->id);
        }

        // Multiple documents - create ZIP
        return $this->createZipDownload(
            $documents,
            Str::slug($element->nama) . '_' . time() . '.zip'
        );
    }

    // Download all files from a bab (can be parent or child)
    public function downloadBab($babId)
    {
        $bab = AkreBabElement::with(['children.elements.documents', 'elements.documents'])->findOrFail($babId);
        $documents = $bab->getAllDocuments();

        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found'], 404);
        }

        return $this->createZipDownload(
            $documents,
            Str::slug($bab->nama) . '_' . time() . '.zip',
            $bab
        );
    }

    // Download all files from a chapter
    public function downloadChapter($chapterId)
    {
        $chapter = AkreChapter::with(
            'babs.elements.documents',
            'babs.children.elements.documents'
        )->findOrFail($chapterId);
        // documents nya
        $documents = $chapter->getAllDocuments();

        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No documents found'], 404);
        }

        return $this->createZipDownload(
            $documents,
            Str::slug($chapter->nama) . '_' . time() . '.zip',
            $chapter
        );
    }

    // Helper: Create ZIP with folder structure
    private function createZipDownload($documents, $zipName, $parent = null)
    {
        $zip = new ZipArchive();
        $zipPath = storage_path('app/temp/' . $zipName);

        // Create temp directory if not exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create ZIP file'], 500);
        }

        // Add documents with folder structure
        foreach ($documents as $document) {
            $filePath = Storage::disk('public')->path($document->path);

            if (file_exists($filePath)) {
                // Create folder structure in ZIP
                $folderPath = $this->getFolderStructure($document, $parent);
                $zip->addFile($filePath, $folderPath . '/' . $document->filename);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    // Helper: Get folder structure for ZIP
    private function getFolderStructure($document, $parent = null)
    {
        $element = $document->elements->first();

        if (!$element) {
            return 'documents';
        }

        $bab = $element->bab; // This could be a parent or child bab

        if (!$bab) {
            return 'documents';
        }

        $structure = [];

        // Build path based on parent type
        if ($parent instanceof AkreChapter) {
            // Downloading from chapter level
            if ($bab->parent_id) {
                // This is a sub-bab (child)
                $parentBab = $bab->parent;
                $structure[] = Str::slug($parentBab->no . " " . $parentBab->nama); // Parent bab
                $structure[] = Str::slug($bab->no . " " . $bab->nama); // Sub-bab
                $structure[] = Str::slug($element->nomor); // Element
            } else {
                // This is a parent bab
                $structure[] = Str::slug($bab->nama);
                $structure[] = Str::slug($element->nomor); // Element
            }
            $structure[] = Str::slug($element->nama);
        } elseif ($parent instanceof AkreBabElement) {
            // Downloading from bab level
            if ($bab->id === $parent->id) {
                // Direct element under this bab
                $structure[] = Str::slug($element->nama);
            } elseif ($bab->parent_id === $parent->id) {
                // Element is under a sub-bab of this parent bab
                $structure[] = Str::slug($bab->nama); // Sub-bab name
                $structure[] = Str::slug($element->nama);
            }
        } else {
            // Default structure - full hierarchy
            $chapter = $bab->parent_id ? $bab->parent->chapter : $bab->chapter;

            if ($chapter) {
                $structure[] = Str::slug($chapter->nama);
            }

            if ($bab->parent_id) {
                // This is a sub-bab
                $parentBab = $bab->parent;
                $structure[] = Str::slug($parentBab->nama);
                $structure[] = Str::slug($bab->nama);
            } else {
                // This is a parent bab
                $structure[] = Str::slug($bab->nama);
            }

            $structure[] = Str::slug($element->nama);
        }

        return implode('/', array_filter($structure));
    }
}
