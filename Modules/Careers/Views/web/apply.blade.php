@extends('careers::web.layout')

@php
    $pageTitle = 'Apply for ' . $job->title . ' | ' . $siteName;
    $pageDescription = 'Submit your application for ' . $job->title . ' at ' . $siteName . '.';
@endphp

@section('content')
    <section class="career-hero">
        <div class="career-shell">
            <div class="career-hero__panel">
                <span class="career-eyebrow">Application form</span>
                <h1 style="max-width: 12ch;">Apply for {{ $job->title }}</h1>
                <p class="career-subtitle">{{ $job->location }} | {{ \Modules\Careers\Models\Job::jobTypes()[$job->job_type] ?? $job->job_type }} | {{ $job->experience }}</p>
            </div>
        </div>
    </section>

    <section class="career-shell career-grid career-grid--sidebar">
        <aside class="career-sidebar">
            <h3>Before You Submit</h3>
            <div class="career-list">
                <div><strong>Role</strong><div class="career-meta">{{ $job->title }}</div></div>
                <div><strong>Team</strong><div class="career-meta">{{ $job->category?->name ?? 'General' }}</div></div>
                <div><strong>Resume</strong><div class="career-meta">PDF, DOC, or DOCX up to 5 MB</div></div>
                <div><strong>Response</strong><div class="career-meta">A confirmation email is sent after submission.</div></div>
            </div>
        </aside>

        <div class="career-form-card">
            @if(session('success'))
                <div class="career-flash">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="career-errors">
                    <strong>Please fix the following:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h2>Candidate Details</h2>
            <p class="career-form-note">Tell us about yourself and upload your resume. Cover letter and LinkedIn are optional but helpful.</p>

            <form method="POST" action="{{ route('careers.apply.submit', $job->slug) }}" enctype="multipart/form-data" class="career-list">
                @csrf
                <div class="career-field">
                    <label for="career-apply-name">Full Name</label>
                    <input id="career-apply-name" class="career-input" type="text" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="career-field">
                    <label for="career-apply-email">Email</label>
                    <input id="career-apply-email" class="career-input" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="career-field">
                    <label for="career-apply-phone">Phone</label>
                    <input id="career-apply-phone" class="career-input" type="text" name="phone" value="{{ old('phone') }}" required>
                </div>
                <div class="career-field">
                    <label for="career-apply-linkedin">LinkedIn URL</label>
                    <input id="career-apply-linkedin" class="career-input" type="url" name="linkedin_url" value="{{ old('linkedin_url') }}" placeholder="https://www.linkedin.com/in/your-profile">
                </div>
                <div class="career-field">
                    <label for="career-apply-resume">Resume</label>
                    <input id="career-apply-resume" class="career-input" type="file" name="resume" accept=".pdf,.doc,.docx" required>
                </div>
                <div class="career-field">
                    <label for="career-apply-cover-letter">Cover Letter</label>
                    <textarea id="career-apply-cover-letter" class="career-textarea" name="cover_letter" placeholder="Share why you are a strong fit for this role...">{{ old('cover_letter') }}</textarea>
                </div>
                <div class="career-actions">
                    <button type="submit" class="career-btn">Submit Application</button>
                    <a href="{{ route('careers.show', $job->slug) }}" class="career-btn--ghost">Back to Job</a>
                </div>
            </form>
        </div>
    </section>
@endsection
