<?php

namespace App\Http\Controllers;

use App\Models\CareerPrediction;
use App\Models\User;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WorkController extends Controller
{
    public function index()
    {
        $works = Work::with('user:id,username,name')->latest()->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
            ],
            'data' => $works,
        ]);
    }

    public function showByUsername($username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $works = Work::where('user_id', $user->id)
            ->with('user:id,username,name')
            ->latest()
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
            ],
            'data' => $works,
        ]);
    }

    public function showByAuthUser()
    {
        $works = Work::where('user_id', Auth::id())
            ->with('user:id,username,first_name,last_name')
            ->latest()
            ->get();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
            ],
            'data' => $works,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_inspires' => 'required|string',
            'work_prefer' => 'required|string',
            'work_challenge' => 'required|string',
            'work_hardskills' => 'required|array',
            'work_softskills' => 'required|array',
            'work_roles' => 'required|string',
        ]);

        $work = Work::create([
            'user_id' => Auth::id(),
            'work_inspires' => $validated['work_inspires'],
            'work_prefer' => $validated['work_prefer'],
            'work_challenge' => $validated['work_challenge'],
            'work_hardskills' => json_encode($validated['work_hardskills']),
            'work_softskills' => json_encode($validated['work_softskills']),
            'work_roles' => $validated['work_roles'],
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 201,
            ],
            'data' => $work,
        ]);
    }

    public function update(Request $request, $id)
    {
        $work = Work::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'work_inspires' => 'sometimes|required|string',
            'work_prefer' => 'sometimes|required|string',
            'work_challenge' => 'sometimes|required|string',
            'work_hardskills' => 'sometimes|required|array',
            'work_softskills' => 'sometimes|required|array',
            'work_roles' => 'sometimes|required|string',
        ]);

        $work->update([
            'work_inspires' => $validated['work_inspires'] ?? $work->work_inspires,
            'work_prefer' => $validated['work_prefer'] ?? $work->work_prefer,
            'work_challenge' => $validated['work_challenge'] ?? $work->work_challenge,
            'work_hardskills' => isset($validated['work_hardskills'])
                ? json_encode($validated['work_hardskills'])
                : $work->work_hardskills,
            'work_softskills' => isset($validated['work_softskills'])
                ? json_encode($validated['work_softskills'])
                : $work->work_softskills,
            'work_roles' => $validated['work_roles'] ?? $work->work_roles,
        ]);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
            ],
            'data' => $work,
        ]);
    }

    public function destroy($id)
    {
        $work = Work::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $work->delete();

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Work deleted successfully',
            ],
        ]);
    }

    public function predictCareerPath()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 401,
                        'message' => 'Unauthorized user.',
                    ]
                ], 401);
            }

            $work = Work::where('user_id', $user->id)->first();

            if (!$work) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 404,
                        'message' => 'Data work tidak ditemukan untuk user ini.',
                    ]
                ], 404);
            }

            $geminiApiKey = env('GEMINI_API_KEY');

            if (!$geminiApiKey) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 500,
                        'message' => 'GEMINI_API_KEY belum diset di .env',
                    ]
                ], 500);
            }

            $prompt = "
Kamu adalah *AI Career Coach Profesional di Indonesia*. 
Tugasmu: memberikan rekomendasi **5 jalur karier terbaik di Indonesia** untuk user berdasarkan data berikut:

Data user:
" . json_encode($work, JSON_PRETTY_PRINT) . "

Kamu harus mengembalikan output **DALAM FORMAT JSON VALID** (tanpa ```json atau teks tambahan) dengan struktur berikut:

{
  \"career_paths\": [
    {
      \"title\": \"string (nama karier)\",
      \"description\": \"penjelasan umum tentang karier ini di Indonesia\",
      \"roadmap\": [
        {
          \"step\": \"tahapan belajar atau karier\",
          \"explanation\": \"penjelasan rinci tahapan tersebut dalam konteks Indonesia\"
        }
      ],
      \"average_salary\": \"dalam rentang Rupiah, contoh: 'Rp 8.000.000 - Rp 20.000.000 per bulan'\",
      \"requirements\": [\"keahlian, kemampuan, atau sertifikasi yang dibutuhkan\"],
      \"recommended_majors\": [\"jurusan kuliah yang cocok di Indonesia\"],
      \"recommended_companies\": [\"perusahaan atau institusi relevan di Indonesia\"]
    }
  ]
}

Instruksi tambahan:
- Gunakan studi kasus dan realitas pasar kerja di **Indonesia tahun 2025**.
- Semua salary harus dalam **Rupiah**.
- Jelaskan roadmap secara mendalam, bukan hanya daftar langkah.
- Hindari istilah terlalu teknis yang tidak umum di Indonesia.
- Pastikan JSON valid dan lengkap.
";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $geminiApiKey,
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => $response->status(),
                        'message' => 'Gagal memanggil Gemini API.',
                        'raw' => $response->body(),
                    ]
                ], $response->status());
            }

            $data = $response->json();

            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$text) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 500,
                        'message' => 'Tidak ada respon dari Gemini API.',
                        'raw' => $response->body(),
                    ]
                ], 500);
            }

            $cleanText = preg_replace('/^```(json)?\n|\n```$/', '', trim($text));

            $careerPaths = json_decode($cleanText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'meta' => [
                        'status' => 'error',
                        'statusCode' => 500,
                        'message' => 'Response Gemini bukan JSON valid (setelah cleaning)',
                        'raw' => $cleanText,
                    ]
                ], 500);
            }

            $prediction = CareerPrediction::updateOrCreate(
                ['user_id' => $user->id],
                ['data' => $careerPaths]
            );

            return response()->json([
                'meta' => [
                    'status' => 'success',
                    'statusCode' => 200,
                    'message' => 'Prediction generated successfully',
                ],
                'data' => $prediction->data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 500,
                    'message' => $e->getMessage(),
                ]
            ], 500);
        }
    }

    public function history()
    {
        $user = Auth::user();

        $prediction = CareerPrediction::where('user_id', $user->id)->first();

        if (!$prediction) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'statusCode' => 404,
                    'message' => 'Career prediction not found',
                ],
            ], 404);
        }

        return response()->json([
            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
            ],
            'data' => [
                'career_paths' => $prediction->data['career_paths'] ?? [],
                'created_at' => $prediction->created_at,
            ],
        ]);
    }

    public function hasCareerPrediction()
    {
        $user = Auth::user();

        $exists = CareerPrediction::where('user_id', $user->id)->exists();

        return response()->json([

            'meta' => [
                'status' => 'success',
                'statusCode' => 200,
                'message' => 'Check for career prediction completed',
            ],
            'data' => [
                'hasCareerPrediction' => $exists,
            ],
        ]);
    }
}
