@extends('master')

@section('title', 'Home')

@section('content')

	<nav class="navbar navbar-dark bg-primary">
		<div></div>
		<button id="newcreditbtn" class="btn btn-outline-light" type="button" data-toggle="modal" data-target="#credit-modal">New Credit</button>
		<h5 class="col-white">Credit Management Application</h5>
		<button class="btn btn-outline-light payment" type="button" data-toggle="modal" data-target="#payment-modal">New Payment</button>
		<div></div>
	</nav>
	<main class="container">
		<div class="card text-left">
			<h5 style="padding: 15px;">All Active Credits</h5>
			<table class="table table-sm">
				<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Credited User</th>
					<th scope="col">Credit debt</th>
					<th scope="col">Terms</th>
					<th scope="col">Monthly payment</th>
				</tr>
				</thead>
				<tbody id="top-tbody">
				</tbody>
			</table>
		</div>
	</main>



<!-- Payments Modal -->

<div class="modal fade" id="payment-modal" tabindex="-1" role="dialog" aria-labelledby="payment-modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="payment-modal">Make new payment for:</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<select id="dropdown" class="form-select">
					<option value=0>Select ID</option>
					@foreach ($dropdown_active as $activeID)
						<option value="{{ $activeID }}">{{ $activeID }}</option>
					@endforeach
						
				</select>
				
				<form class="form-inline">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1">BGN</span>
						</div>
						<input id="payment_amount" type="text" class="form-control" placeholder="Payment Amount" aria-describedby="basic-addon1">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button id='makepayment' type="button" class="btn btn-success" data-dismiss="modal">Make Payment</button>
			</div>
		</div>
	</div>
</div>

<!-- New Credit Modal -->

<div class="modal fade" id="credit-modal" tabindex="-1" role="dialog" aria-labelledby="credit-modal" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="credit-modal">Enter new credit data:</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form class="form-inline">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1">User</span>
						</div>
						<input id="username" type="text" class="form-control" placeholder="Name" aria-describedby="basic-addon1">
					</div>
				</form>
				<form class="form-inline">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1">BGN</span>
						</div>
						<input id="amount" type="text" class="form-control" placeholder="Amount" aria-describedby="basic-addon1">
					</div>
				</form>
				
				<form class="form-inline">
					<div class="input-group">
						<div class="input-group-prepend">
							<span class="input-group-text" id="basic-addon1">MTH</span>
						</div>
						<input id="terms" type="text" class="form-control" placeholder="Terms" aria-describedby="basic-addon1">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button id="createnew" type="button" class="btn btn-success" data-dismiss="modal">Create</button>
			</div>
		</div>
	</div>
</div>

<script>

$(document).ready(function () {
	var creditsTableObjs = JSON.parse({!! json_encode($credits_table) !!});
	var name = '';
	var amount = 0;
	var terms = 0;
	var id = 0;
	var payment_amount = 0;

	$.each(creditsTableObjs, function(key, value){
		$('#top-tbody').append(
			'<tr>' +
					'<td scope="col">'+ value['ID'] +'</td>' +
					'<td scope="col">'+ value['credited_user'] +'</td>' +
					'<td scope="col">'+ value['remaining_amount'] +'</td>' +
					'<td scope="col">'+ value['terms'] +'</td>' +
					'<td scope="col">'+ value['mounthly_payment'] +'</td>' +
			'</tr>'
		)
	});

	$("#createnew").on('click', function(event){
		name = $('#username').val();
		amount = $('#amount').val();
		terms = $('#terms').val();
		$.ajax({
			type: 'POST',
			url: '/new',
			datatype: 'json',
			data: {
				name : name,
				amount : amount,
				terms : terms,
			},
			success: function(msg){
				alert(msg);
			}, 
			error: function(msg){
				alert(msg);
			}
		});
		
		window.location.reload(true);
	});

	$("#makepayment").on('click', function(event){
		id = $('#dropdown').val();
		payment_amount = $('#payment_amount').val();

		$.ajax({
			type: 'GET',
			url: '/payment',
			datatype: 'json',
			data: {
				id : id,
				payment_amount : payment_amount,
			},
			success: function(msg){
				alert(msg);
			}, 
			error: function(msg){
				alert(msg);
			}
		});
		
		window.location.reload(true);
	});
});

</script>

@endsection