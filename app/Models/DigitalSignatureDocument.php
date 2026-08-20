<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalSignatureDocument extends Model
{
    protected $table = 'digital_signature_documents';
    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(DigitalSignatureApproval::class, 'digital_signature_document_id', 'id')->orderBy('step_order', 'asc');
    }

    public function scopeMySubmissions($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the signer with the highest rank (lowest urutan value) in the approval chain.
     */
    public function getHighestRankSigner()
    {
        $approvals = $this->approvals()->with(['user.karyawan.jabatan.tingkat'])->get();

        if ($approvals->isEmpty()) {
            return $this->user;
        }

        $highestSigner = null;
        $highestUrutan = 99999;

        foreach ($approvals as $appr) {
            $user = $appr->user;
            if (!$user) continue;

            $urutan = 999;
            $karyawan = $user->karyawan;
            if ($karyawan) {
                $jabatan = $karyawan->jabatan()->with('tingkat')->first();
                if ($jabatan && $jabatan->tingkat) {
                    $urutan = (int) ($jabatan->tingkat->urutan ?? 999);
                }
            }

            if ($urutan < $highestUrutan) {
                $highestUrutan = $urutan;
                $highestSigner = $user;
            }
        }

        return $highestSigner ?: ($approvals->first()?->user ?: $this->user);
    }

    /**
     * Check if the document is waiting for approval from a specific user.
     */
    public function isPendingForUser(int $userId): bool
    {
        if ($this->status === 'rejected') {
            return false;
        }

        $userApproval = $this->approvals()->where('user_id', $userId)->where('status', 'pending')->first();
        if (!$userApproval) {
            return false;
        }

        // Check if all previous steps are approved
        $previousUnapprovedCount = $this->approvals()
            ->where('step_order', '<', $userApproval->step_order)
            ->where('status', '!=', 'approved')
            ->count();

        return $previousUnapprovedCount === 0;
    }
}
