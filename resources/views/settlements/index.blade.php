@extends('layouts.app')

@section('title', 'Règlements - ' . $collocation->name)

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800"><i class="fa-solid fa-hand-holding-dollar mr-2 text-green-600"></i>
                Règlements Simplifiés</h1>
            <a href="{{ route('collocation', $collocation->id) }}" class="text-blue-600 hover:text-blue-800">&larr; Retour
                au Dashboard</a>
        </div>

        @if(empty($transactions))
            <div class="bg-green-50 rounded-xl p-12 text-center border border-green-200 shadow-sm">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-check text-4xl text-green-500"></i>
                </div>
                <h2 class="text-2xl font-bold text-green-800 mb-2">Tout le monde est à jour !</h2>
                <p class="text-green-600">Aucune dette n'a été trouvée dans cette colocation.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-gray-50 border-b border-gray-100">
                    <p class="text-gray-600 text-sm">Ces transactions ont été simplifiées automatiquement pour minimiser le
                        nombre de virements (Algorithme Greedy).</p>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($transactions as $txn)
                        <div class="p-6 flex flex-col sm:flex-row items-center justify-between transition hover:bg-gray-50">

                            <div class="flex items-center w-full sm:w-auto mb-4 sm:mb-0">
                                <!-- Debtor -->
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-12 h-12 bg-red-100 text-red-700 rounded-full flex items-center justify-center font-bold text-lg mb-2 shadow-sm border border-red-200">
                                        {{ substr($txn['from_name'], 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-800">{{ $txn['from_name'] }}</span>
                                    @if(auth()->id() == $txn['from_id']) <span
                                    class="text-xs bg-gray-200 px-2 py-1 rounded mt-1">Vous</span> @endif
                                </div>

                                <!-- Arrow flow -->
                                <div class="mx-6 flex flex-col items-center">
                                    <span class="text-xl font-black text-gray-800">{{ number_format($txn['amount'], 2) }} DH</span>
                                    <i class="fa-solid fa-arrow-right-long text-gray-400 mt-1"></i>
                                </div>

                                <!-- Creditor -->
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-12 h-12 bg-green-100 text-green-700 rounded-full flex items-center justify-center font-bold text-lg mb-2 shadow-sm border border-green-200">
                                        {{ substr($txn['to_name'], 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-800">{{ $txn['to_name'] }}</span>
                                    @if(auth()->id() == $txn['to_id']) <span
                                    class="text-xs bg-gray-200 px-2 py-1 rounded mt-1">Vous</span> @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            @if(auth()->id() === $txn['to_id'])
                                <button onclick="markAsPaid({{ $txn['from_id'] }}, {{ $txn['amount'] }})"
                                    class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition shadow-sm flex items-center justify-center">
                                    <i class="fa-solid fa-check-double mr-2"></i> Marquer Payé
                                </button>
                            @elseif(auth()->id() === $txn['from_id'])
                                <span class="text-sm text-gray-500 italic bg-gray-100 px-4 py-2 rounded-lg"><i
                                        class="fa-solid fa-clock mr-1"></i> En attente de confirmation</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        function markAsPaid(senderId, amount) {
            if (!confirm('Confirmez-vous avoir reçu ce montant sur votre compte personnel ? Cette action affectera la balance.')) return;

            fetch('/api/payments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    receiver_id: {{ auth()->id() }},
                    sender_id: senderId,
                    amount: amount
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        fetch(`/api/payments/${data.data.id}/mark-paid`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        })
                            .then(res => res.json())
                            .then(res => {
                                if (res.success) {
                                    if (window.showToast) window.showToast('Paiement marqué comme reçu', 'success');
                                    else alert('Paiement marqué comme reçu');
                                    setTimeout(() => window.location.reload(), 1000);
                                }
                            });
                    }
                });
        }
    </script>
@endsection