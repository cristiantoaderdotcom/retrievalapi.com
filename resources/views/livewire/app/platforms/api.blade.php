<div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <flux:icon name="code-bracket" class="text-blue-500 size-8" />
            <flux:heading size="xl">API Integration</flux:heading>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column: API Token Management -->
        <div class="lg:col-span-1">
            <flux:card class="p-6">
                <flux:heading size="lg" class="mb-4">API Tokens</flux:heading>
                
                <p class="text-gray-600 dark:text-gray-300 mb-4">
                    Create API tokens to integrate your AI assistant with your own applications.
                </p>
                
                <form wire:submit="createToken" class="mb-6">
                    <flux:field class="mb-4">
                        <flux:label for="tokenName">Token Name</flux:label>
                        <flux:input 
                            wire:model="tokenName" 
                            id="tokenName"
                            placeholder="Mobile App" 
                            hint="Give your token a descriptive name to identify its use"
                        />
                    </flux:field>
                    
                    <flux:button type="submit" variant="primary" class="w-full">
                        Generate API Token
                    </flux:button>
                </form>
                
                <div>
                    <flux:heading size="sm" class="mb-3">Your API Tokens</flux:heading>
                    
                    @if(count($tokens) > 0)
                        <div class="space-y-3">
                            @foreach($tokens as $token)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">{{ $token->name }}</p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                Created: {{ $token->created_at->format('M d, Y') }}
                                            </p>
                                            @if($token->last_used_at)
                                                <p class="text-xs text-gray-500">
                                                    Last used: {{ $token->last_used_at->format('M d, Y H:i') }}
                                                </p>
                                            @endif
                                        </div>
                                        <button 
                                            wire:click="deleteToken({{ $token->id }})" 
                                            class="text-red-500 hover:text-red-700" 
                                            title="Delete token"
                                        >
                                            <flux:icon name="trash" class="size-4" />
                                        </button>
                                    </div>
                                    <div class="mt-2">
                                        <div class="bg-gray-100 dark:bg-gray-800 rounded p-2 flex justify-between items-center">
                                            <code class="text-xs truncate">{{ $token->token }}</code>
                                            <button 
                                                onclick="navigator.clipboard.writeText('{{ $token->token }}'); this.textContent = 'Copied!';"
                                                class="text-xs text-blue-500 hover:text-blue-700"
                                            >
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">No API tokens yet. Create one using the form above.</p>
                    @endif
                </div>
            </flux:card>
        </div>
        
        <!-- Right column: API Documentation -->
        <div class="lg:col-span-2">
            <flux:card class="p-6">
                <flux:heading size="lg" class="mb-4">API Documentation</flux:heading>
                
                <div class="mb-6">
                    <flux:heading size="md" class="mb-2">Endpoint</flux:heading>
                    <div class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm">
                        POST {{ url('/api/v1/message') }}
                    </div>
                    
                    <flux:heading size="md" class="mt-5 mb-2">Authentication</flux:heading>
                    <p class="text-gray-600 dark:text-gray-300 mb-2">
                        Use Bearer token authentication with the API token generated on the left.
                    </p>
                    <div class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm">
                        Authorization: Bearer YOUR_API_TOKEN
                    </div>
                </div>
                
                <div class="mb-6">
                    <flux:heading size="md" class="mb-2">Request Parameters</flux:heading>
                    
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 px-2">Parameter</th>
                                <th class="text-left py-3 px-2">Type</th>
                                <th class="text-left py-3 px-2">Required</th>
                                <th class="text-left py-3 px-2">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr>
                                <td class="py-2 px-2 font-mono text-sm">message</td>
                                <td class="py-2 px-2">String</td>
                                <td class="py-2 px-2">Yes</td>
                                <td class="py-2 px-2">The message to send to the AI</td>
                            </tr>
                            <tr>
                                <td class="py-2 px-2 font-mono text-sm">conversation_uuid</td>
                                <td class="py-2 px-2">String (UUID)</td>
                                <td class="py-2 px-2">No</td>
                                <td class="py-2 px-2">UUID of an existing conversation to continue</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="mb-6">
                    <flux:heading size="md" class="mb-2">Response Format</flux:heading>
                    
                    <div class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm overflow-auto">
<pre>{
  "success": true,
  "conversation_uuid": "123e4567-e89b-12d3-a456-426614174000",
  "conversation_created": true, // Only true if a new conversation was created
  "response": "Here is the AI's response to your message",
  "tokens": 123
}</pre>
                    </div>
                </div>
                
                <div class="mb-6">
                    <flux:heading size="md" class="mb-4">Code Examples</flux:heading>
                    
                    <div x-data="{ activeTab: 'curl' }" class="mb-6">
                        <div class="flex space-x-1 mb-2 border-b border-gray-200 dark:border-gray-700">
                            <button 
                                @click="activeTab = 'curl'" 
                                :class="{ 'text-blue-600 border-b-2 border-blue-600': activeTab === 'curl', 'text-gray-500': activeTab !== 'curl' }"
                                class="px-4 py-2 font-medium text-sm focus:outline-none"
                            >
                                cURL
                            </button>
                            <button 
                                @click="activeTab = 'javascript'" 
                                :class="{ 'text-blue-600 border-b-2 border-blue-600': activeTab === 'javascript', 'text-gray-500': activeTab !== 'javascript' }"
                                class="px-4 py-2 font-medium text-sm focus:outline-none"
                            >
                                JavaScript
                            </button>
                            <button 
                                @click="activeTab = 'php'" 
                                :class="{ 'text-blue-600 border-b-2 border-blue-600': activeTab === 'php', 'text-gray-500': activeTab !== 'php' }"
                                class="px-4 py-2 font-medium text-sm focus:outline-none"
                            >
                                PHP
                            </button>
                            <button 
                                @click="activeTab = 'python'" 
                                :class="{ 'text-blue-600 border-b-2 border-blue-600': activeTab === 'python', 'text-gray-500': activeTab !== 'python' }"
                                class="px-4 py-2 font-medium text-sm focus:outline-none"
                            >
                                Python
                            </button>
                        </div>
                        
                        <div x-show="activeTab === 'curl'" class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm overflow-auto">
<pre>curl -X POST {{ url('/api/v1/message') }} \
     -H "Authorization: Bearer YOUR_API_TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "message": "Hello, can you help me with something?",
       "conversation_uuid": "optional-existing-conversation-uuid"
     }'</pre>
                        </div>
                        
                        <div x-show="activeTab === 'javascript'" class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm overflow-auto">
<pre>const response = await fetch('{{ url('/api/v1/message') }}', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_API_TOKEN',
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    message: 'Hello, can you help me with something?',
    conversation_uuid: 'optional-existing-conversation-uuid' // Optional
  })
});

const data = await response.json();
console.log(data.response);</pre>
                        </div>
                        
                        <div x-show="activeTab === 'php'" class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm overflow-auto">
<pre>&lt;?php
$ch = curl_init();

$payload = json_encode([
    'message' => 'Hello, can you help me with something?',
    'conversation_uuid' => 'optional-existing-conversation-uuid' // Optional
]);

curl_setopt($ch, CURLOPT_URL, '{{ url('/api/v1/message') }}');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_API_TOKEN',
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload)
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
echo $data['response'];
?></pre>
                        </div>
                        
                        <div x-show="activeTab === 'python'" class="bg-gray-100 dark:bg-gray-800 rounded p-3 font-mono text-sm overflow-auto">
<pre>import requests
import json

url = '{{ url('/api/v1/message') }}'
headers = {
    'Authorization': 'Bearer YOUR_API_TOKEN',
    'Content-Type': 'application/json'
}
payload = {
    'message': 'Hello, can you help me with something?',
    'conversation_uuid': 'optional-existing-conversation-uuid'  # Optional
}

response = requests.post(url, headers=headers, data=json.dumps(payload))
data = response.json()
print(data['response'])</pre>
                        </div>
                    </div>
                </div>
                
                <div>
                    <flux:heading size="md" class="mb-2">Maintaining Conversations</flux:heading>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        To maintain a conversation thread, save the <code>conversation_uuid</code> from the first API response, 
                        and include it in subsequent requests. This allows the AI to maintain context and provide more coherent responses.
                    </p>
                    
                    <flux:callout icon="information-circle" color="blue" class="mb-4">
                        <flux:callout.text>
                            The API uses the same AI configuration as your workspace, inheriting your tone, style, and knowledge base settings.
                        </flux:callout.text>
                    </flux:callout>
                    
                    <flux:callout icon="shield-exclamation" color="amber">
                        <flux:callout.text>
                            Keep your API tokens secure. Anyone with your token can make API requests on behalf of your workspace.
                        </flux:callout.text>
                    </flux:callout>
                </div>
            </flux:card>
        </div>
    </div>
</div>
