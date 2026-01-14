use App\Models\User;
use App\Models\Business;

$user = User::where('email', 'chitra@example.com')->first();

if (!$user) {
echo "User not found!\n";
exit;
}

echo "User: {$user->email}\n";
echo "User ID: {$user->id}\n\n";

$businesses = $user->businesses()->get();
echo "Number of businesses: {$businesses->count()}\n\n";

foreach ($businesses as $business) {
echo "Business: {$business->name}\n";
echo "Business ID: {$business->id}\n";
echo "Role: {$business->pivot->role}\n";
echo "Status: {$business->pivot->status}\n";
echo "Joined At: {$business->pivot->joined_at}\n";
echo "---\n";
}