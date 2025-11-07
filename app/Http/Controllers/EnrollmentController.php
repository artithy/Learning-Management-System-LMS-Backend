<?php

namespace App\Http\Controllers;

use App\Models\CourseModel;
use App\Models\enrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    public function enroll(Request $request)
    {
        $student_id = $request->attributes->get('auth_user')['id'];
        $request->validate([
            'course_id' => 'required|integer',
            'payment_method' => 'required|string',
            'payment_transaction_id' => 'required|string',
        ]);

        $existing = enrollment::where('student_id', $student_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Student already enrolled in this course'], 400);
        }

        $enrollment = enrollment::create([
            'student_id' => $student_id,
            'course_id' => $request->course_id,
            'payment_status' => 'completed',
            'payment_method' => $request->payment_method,
            'payment_transaction_id' => $request->payment_transaction_id,
            'amount' => $request->amount ?? 0,
        ]);

        return response()->json([
            'message' => 'Enrollment successful',
            'enrollment' => $enrollment
        ]);
    }

    public function allEnrollments()
    {
        $enrollments = enrollment::with(['student', 'course'])->get();
        return response()->json(['enrollments' => $enrollments]);
    }

    public function studentEnrollments(Request $request)
    {
        $student_id = $request->attributes->get('auth_user')['id'];
        $enrollments = enrollment::with('course')->where('student_id', $student_id)->get();
        return response()->json(['enrollments' => $enrollments]);
    }

    public function createPayment(Request $request)
    {
        set_time_limit(120);

        try {
            $student = $request->attributes->get('auth_user');
            if (!$student) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $request->validate([
                'course_id' => 'required|integer',
                'amount' => 'required|numeric',
                'user_name' => 'required|string',
                'user_email' => 'required|email',
                'user_phone' => 'required|string'
            ]);

            $course = CourseModel::find($request->course_id);
            if (!$course) {
                return response()->json([
                    'status' => false,
                    'message' => 'Course not found'
                ], 404);
            }
            $courseId = is_array($course) ? $course['id'] : $course->id;
            $studentId = is_array($student) ? $student['id'] : $student->id;

            $appKey    = "";
            $secretKey = "";
            $bearerToken = "Bearer " . base64_encode($appKey . ":" . md5($secretKey . time()));

            $data = [
                "order" => [
                    "amount" => (float) $request->amount,
                    "currency" => "BDT",
                    "redirect_url" => "http://localhost:5074/student/payment-success?invoice={invoice}&course_id={$courseId}&student_id={$studentId}"
                ],
                "product" => [
                    "name" => $course->title ?? 'Course Purchase',
                    "description" => "Purchase of course: " . ($course->title ?? 'N/A')
                ],
                "billing" => [
                    "customer" => [
                        "name" => $request->user_name,
                        "email" => $request->user_email,
                        "phone" => $request->user_phone,
                        "address" => [
                            "street" => $request->user_address ?? 'Not provided',
                            "city" => "Dhaka",
                            "state" => "Dhaka",
                            "zipcode" => 1207,
                            "country" => "BD"
                        ]
                    ]
                ]
            ];

            $response = Http::withHeaders([
                "Authorization" => $bearerToken,
                "Content-Type" => "application/json"
            ])->timeout(120)
                ->post("https://api-sandbox.portpos.com/payment/v2/invoice", $data);

            if ($response->failed()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment API request failed',
                    'error' => $response->body()
                ], 500);
            }

            $result = $response->json();

            $invoiceId = $result['data']['invoice_id'] ?? null;
            $orderId = $result['data']['order_id'] ?? null;
            $paymentUrl = $result['data']['action']['url'] ?? null;

            if ($invoiceId && $paymentUrl) {
                return response()->json([
                    'status' => true,
                    'invoice_id' => $invoiceId,
                    'order_id' => $orderId,
                    'payment_url' => $paymentUrl
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Payment creation failed',
                'error' => $result
            ], 500);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment request timeout or failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Payment request failed (Exception)',
                'error_message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        $invoiceId = $request->query('invoice');
        $courseId = $request->query('course_id');
        $student_id = $request->query('student_id');

        $appKey    = "";
        $secretKey = "";
        $bearerToken = "Bearer " . base64_encode($appKey . ":" . md5($secretKey . time()));

        $response = Http::withHeaders([
            "Authorization" => $bearerToken,
            "Content-Type" => "application/json"
        ])->get("https://api-sandbox.portpos.com/payment/v2/invoice/$invoiceId");

        $data = $response->json();
        $status = $data['data']['order']['status'] ?? null;

        $orderId = $data['data']['order']['order_id'] ?? null;

        if ($status === 'ACCEPTED') {
            $existing = enrollment::where('student_id', $student_id)
                ->where('course_id', $courseId)
                ->first();

            if (!$existing) {
                enrollment::create([
                    'student_id' => $student_id,
                    'course_id' => $courseId,
                    'payment_status' => 'completed',
                    'payment_method' => 'Portpos',
                    'payment_transaction_id' => $invoiceId,
                    'amount' => $data['data']['order']['amount'] ?? 0,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment verified & enrollment successful',
                'order_id' => $orderId,
                'invoice_id' => $invoiceId,
                'payment_status' => $status
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Payment not successful',
            'payment_status' => $status,
            'api_response' => $data
        ]);
    }

    public function dashboardStats()
    {
        $today = Carbon::today();
        $enrollmentToday = enrollment::whereDate('created_at', $today)->count();
        $paymentToday = enrollment::whereDate('created_at', $today)
            ->where('payment_status', 'completed')
            ->count();
        $acceptEnrollment = enrollment::where('payment_status', 'completed')->count();
        $totalPayment = enrollment::where('payment_status', 'completed')->sum('amount');
        return response()->json([
            'enrollment_today' => $enrollmentToday,
            'payment_today' => $paymentToday,
            'accepted_enrollment' => $acceptEnrollment,
            'total_payment' => $totalPayment,
        ]);
    }

    public function enrollmentsByDays()
    {
        $result = enrollment::select(
            DB::raw('DATE(created_at) as day'),
            DB::raw('COUNT(*) as total')
        )
            ->where('created_at', '>=', Carbon::today()->subDays(6))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day', 'ASC')
            ->get();

        $labels = [];
        $data = [];

        foreach ($result as $row) {
            $labels[] = Carbon::parse($row->day)->format('D');
            $data[] = (int)$row->total;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function enrollmentsByHours()
    {
        $result = enrollment::select(
            DB::raw('HOUR(created_at) as hour'),
            DB::raw('COUNT(*) as total')
        )
            ->whereDate('created_at', Carbon::today())
            ->groupBy(DB::raw('HOUR(created_at)'))
            ->orderBy('hour', 'ASC')
            ->get();

        $labels = [];
        $data = [];

        foreach ($result as $row) {
            $labels[] = $row->hour . ":00";
            $data[] = (int)$row->total;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }
}
