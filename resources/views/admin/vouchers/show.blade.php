@extends('layouts.admin')

@section('title', 'Chi Tiết Voucher')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-eye"></i>
                        Chi Tiết Voucher: {{ $voucher->code }}
                    </h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i>
                            Sửa
                        </a>
                        <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông Tin Cơ Bản</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Mã Voucher:</strong></td>
                                            <td><code class="fs-5">{{ $voucher->code }}</code></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Loại:</strong></td>
                                            <td>
                                                <span class="badge {{ $voucher->type === 'PERCENTAGE' ? 'bg-info' : 'bg-primary' }}">
                                                    {{ $voucher->type === 'PERCENTAGE' ? 'Phần trăm' : 'Cố định' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Giá trị:</strong></td>
                                            <td class="fs-5">
                                                {{ $voucher->type === 'PERCENTAGE' ? $voucher->value . '%' : number_format($voucher->value) . ' ₫' }}
                                            </td>
                                        </tr>
                                        @if($voucher->type === 'PERCENTAGE' && $voucher->max_discount > 0)
                                        <tr>
                                            <td><strong>Giảm tối đa:</strong></td>
                                            <td class="fs-5">{{ number_format($voucher->max_discount) }} ₫</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Đơn tối thiểu:</strong></td>
                                            <td class="fs-5">{{ number_format($voucher->min_order_value) }} ₫</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số lượng:</strong></td>
                                            <td class="fs-5">{{ $voucher->quantity }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Đã sử dụng:</strong></td>
                                            <td class="fs-5">{{ $voucher->used_count }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Còn lại:</strong></td>
                                            <td class="fs-5">{{ $voucher->remaining_count }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông Tin Thời Gian</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Ngày bắt đầu:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($voucher->start_date)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày kết thúc:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($voucher->end_date)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                @if(!$voucher->is_valid)
                                                    <span class="badge bg-danger">Không hợp lệ</span>
                                                @elseif($voucher->remaining_count <= 0)
                                                    <span class="badge bg-danger">Đã hết</span>
                                                @elseif((($voucher->quantity - $voucher->remaining_count) / $voucher->quantity) * 100 >= 80)
                                                    <span class="badge bg-warning">Gần hết</span>
                                                @else
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày tạo:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($voucher->createdAt)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Cập nhật cuối:</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($voucher->updatedAt)->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($voucher->usages->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Lịch Sử Sử Dụng ({{ $voucher->usages->count() }} lần)</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>User ID</th>
                                                    <th>Đơn hàng</th>
                                                    <th>Số tiền giảm</th>
                                                    <th>Thời gian sử dụng</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($voucher->usages as $usage)
                                                <tr>
                                                    <td>{{ $usage->id }}</td>
                                                    <td>{{ $usage->user_id }}</td>
                                                    <td>{{ $usage->order_id }}</td>
                                                    <td class="text-success fw-bold">{{ number_format($usage->discount_amount) }} ₫</td>
                                                    <td>{{ \Carbon\Carbon::parse($usage->used_at)->format('d/m/Y H:i') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
