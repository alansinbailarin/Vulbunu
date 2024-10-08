<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Interviews;
use App\Notifications\InterviewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewController extends Controller
{
    public function createInterview(Request $request)
    {
        $user = Auth::user();
        $applicant = Applicant::find($request->applicant_id);
        $applicant_user = $applicant->user;

        $validateData = $request->validate([
            'applicant_id' => 'required|integer|exists:applicants,id',
            'interview_date' => 'required|date|after:yesterday',
            'interview_type' => 'required|string|max:255',
            'interview_link' => 'url|required',
            'interview_duration' => 'required',
            'interview_observation' => 'required|string|max:255',
        ]);

        $interview = Interviews::create($validateData);

        $interview->interviewer_name = $user->name . ' ' . $user->last_name;
        $interview->confirmation_date = $interview->created_at;
        $interview->status = 'approved';

        $applicant_user->notify(new InterviewNotification($interview));

        $interview->save();


        return redirect()->back()->with('success', 'Interview scheduled');
    }

    public function edit(Interviews $interview)
    {
        $interview = Interviews::find($interview->id);
        // get the application information
        $applicant = Applicant::find($interview->applicant_id);

        // Only can view the interview the publisher of the job
        if (Auth::user()->id != $applicant->job->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to view this interview');
        }

        return inertia('Interviews/Edit', [
            'interview' => $interview,
            'applicant' => $applicant,
        ]);
    }

    public function update(Interviews $interview)
    {
        $validateData = request()->validate([
            'interviewer_name' => 'required|string|max:255',
            'interview_date' => 'date',
            'interview_type' => 'required|string|max:255',
            'interview_link' => 'url|required',
            'interview_duration' => 'required',
            'interview_observation' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'interview_feedback' => 'nullable|string|max:255',
        ]);

        // Only can update the publisher of the job
        if (Auth::user()->id != $interview->applicant->job->user_id) {
            return redirect()->back()->with('error', 'You are not authorized to update this interview');
        }

        $interview->update($validateData);

        return redirect()->back()->with('success', 'Interview updated');
    }
}
