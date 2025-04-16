<?php
namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Frontend
    public function index()
    {
        $contact = Contact::first();
        return view('contact.contact', compact('contact'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', 'Thank you for your message!');
    }

    // Admin
    public function adminForm()
    {
        $contact = Contact::firstOrCreate([]);
        return view('contact.edit', compact('contact'));
    }

    public function adminUpdate(Request $request)
    {
        $data = $request->validate([
            'address' => 'nullable|string',
            'phone1' => 'nullable|string',
            'phone2' => 'nullable|string',
            'email1' => 'nullable|email',
            'email2' => 'nullable|email',
            'map_embed' => 'nullable|string',
            'social_facebook' => 'nullable|url',
            'social_instagram' => 'nullable|url',
        ]);

        $contact = Contact::first();
        $contact->update($data);

        return back()->with('success', 'Contact details updated!');
    }
}