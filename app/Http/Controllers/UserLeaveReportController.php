<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\UserLeaveReport;
use App\Models\UserLeaves;

class UserLeaveReportController extends Controller
{
    public function leaveReport($id)
    {
        $totalLeaveData = UserLeaveReport::where('user_id', $id)->first(); 
        $approvedLeaves = UserLeaves::where('leave_status', 'approved')
            ->whereYear('from', date('Y'))
            ->where('user_id', $id)
            ->join('users', 'users.id', '=', 'user_leaves.user_id')
            ->select('user_leaves.*', 'users.first_name', 'users.id', 'users.joining_date', 'users.status')
            ->get();
                                    // dd($approvedLeaves);
            $approvedLeavesCount = $approvedLeaves->count(); 
            
            $pendingLeavesCount = $totalLeaveData->total_leaves - $approvedLeavesCount;

            $userLeaves = UserLeaves::join('users', 'user_leaves.user_id', '=', 'users.id')->where('user_leaves.user_id', $id)->orderBy('id', 'desc')->get(['user_leaves.*', 'users.first_name']);

            // dd($userLeaves);
            return view('leaves.leavereport', [
                    'totalLeaveData' => $totalLeaveData->total_leaves,
                    'approvedLeavesCount' => $approvedLeavesCount,
                    'pendingLeavesCount' => $pendingLeavesCount,
                    'userLeaves' => $userLeaves
            ]);
    }

    public function add_user_holidays(Request $request)
	{
		$validator = \Validator::make($request->all(), [
            'holidays' => 'required|numeric',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->all()]);
        }
    
        $currentYear = date('Y');
        $userTotalLeaves = UserLeaveReport::updateOrCreate(
            ['user_id' => $request->user_id, 'year' => $currentYear],
            [
                'total_leaves' => $request->holidays,
            ]
        );
    
        return response()->json(['status' => 200]);
	}
}
