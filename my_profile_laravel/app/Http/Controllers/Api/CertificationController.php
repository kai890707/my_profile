<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCertificationRequest;
use App\Http\Resources\CertificationResource;
use App\Models\Certification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificationController extends Controller
{
    /**
     * Get all certifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is a salesperson
        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Only salespeople can access certifications',
                ],
            ], 403);
        }

        // Query certifications
        $certifications = $user->certifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => CertificationResource::collection($certifications),
            'message' => 'Certifications retrieved successfully',
        ], 200);
    }

    /**
     * Store a new certification with Base64 file
     */
    public function store(StoreCertificationRequest $request): JsonResponse
    {
        $user = $request->user();

        // Check if user is a salesperson
        if (! $user->isSalesperson()) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'Only salespeople can create certifications',
                ],
            ], 403);
        }

        // Get validated data
        $data = $request->validated();

        // Decode Base64 file
        $fileData = null;
        $fileSize = null;

        if (isset($data['file']) && ! empty($data['file'])) {
            // Remove data URL prefix if present (e.g., "data:image/png;base64,")
            $base64String = $data['file'];
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64String, $matches)) {
                $base64String = $matches[2];
            }

            // Decode Base64
            $fileData = base64_decode($base64String, true);

            if ($fileData === false) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'INVALID_FILE',
                        'message' => 'Invalid Base64 file data',
                    ],
                ], 422);
            }

            $fileSize = strlen($fileData);

            // Check file size (16MB = 16 * 1024 * 1024 bytes)
            $maxSize = 16 * 1024 * 1024;
            if ($fileSize > $maxSize) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'FILE_TOO_LARGE',
                        'message' => 'File size exceeds 16MB limit',
                    ],
                ], 422);
            }
        }

        // Create certification
        $certification = Certification::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'issuer' => $data['issuer'],
            'issue_date' => $data['issue_date'] ?? null,
            'expiry_date' => $data['expiry_date'] ?? null,
            'description' => $data['description'] ?? null,
            'file_mime' => $data['file_mime'] ?? null,
            'file_size' => $fileSize,
            'approval_status' => 'pending', // Requires approval
        ]);

        // Store file_data separately using raw query (because it's not in $fillable)
        if ($fileData !== null) {
            \DB::table('certifications')
                ->where('id', $certification->id)
                ->update(['file_data' => $fileData]);
        }

        // Reload certification to get updated data
        $certification->refresh();

        return response()->json([
            'success' => true,
            'data' => new CertificationResource($certification),
            'message' => 'Certification created successfully. Pending approval.',
        ], 201);
    }

    /**
     * Delete a certification
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        // Find certification
        $certification = Certification::find($id);

        if (! $certification) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Certification not found',
                ],
            ], 404);
        }

        // Check ownership (BR-CERT-001)
        if ($certification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => 'You can only delete your own certifications',
                ],
            ], 403);
        }

        // Delete certification
        $certification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certification deleted successfully',
        ], 200);
    }
}
