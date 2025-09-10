@extends('layouts.admin')

@section('title', 'Sửa Voucher')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit"></i>
                        Sửa Voucher: {{ $voucher->code }}
                    </h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.vouchers.show', $voucher->id) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i>
                            Xem chi tiết
                        </a>
                        <a href="{{ route('admin.vouchers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.vouchers.update', $voucher->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Mã Voucher <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                           id="code" name="code" value="{{ old('code', $voucher->code) }}" 
                                           placeholder="VD: FREESHIP01" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Loại Voucher <span class="text-danger">*</span></label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="">Chọn loại voucher</option>
                                        <option value="NORMAL" {{ old('type', $voucher->type) == 'NORMAL' ? 'selected' : '' }}>Giảm giá cố định</option>
                                        <option value="PERCENTAGE" {{ old('type', $voucher->type) == 'PERCENTAGE' ? 'selected' : '' }}>Giảm giá phần trăm</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="value" class="form-label">Giá Trị <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('value') is-invalid @enderror" 
                                               id="value" name="value" value="{{ old('value', $voucher->value) }}" 
                                               min="0" required>
                                        <span class="input-group-text" id="value-unit">₫</span>
                                    </div>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3" id="max-discount-group" style="display: none;">
                                    <label for="max_discount" class="form-label">Giảm Tối Đa</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('max_discount') is-invalid @enderror" 
                                               id="max_discount" name="max_discount" value="{{ old('max_discount', $voucher->max_discount) }}" 
                                               min="0">
                                        <span class="input-group-text">₫</span>
                                    </div>
                                    @error('max_discount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="min_order_value" class="form-label">Đơn Tối Thiểu <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control @error('min_order_value') is-invalid @enderror" 
                                               id="min_order_value" name="min_order_value" value="{{ old('min_order_value', $voucher->min_order_value) }}" 
                                               min="0" required>
                                        <span class="input-group-text">₫</span>
                                    </div>
                                    @error('min_order_value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Số Lượng <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity', $voucher->quantity) }}" 
                                           min="1" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Đã sử dụng: {{ $voucher->getUsedCount() }} / {{ $voucher->quantity }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" 
                                           value="{{ old('start_date', \Carbon\Carbon::parse($voucher->start_date)->format('Y-m-d\TH:i')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">Ngày Kết Thúc <span class="text-danger">*</span></label>
                                    <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" 
                                           value="{{ old('end_date', \Carbon\Carbon::parse($voucher->end_date)->format('Y-m-d\TH:i')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.vouchers.show', $voucher->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Cập Nhật Voucher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const valueInput = document.getElementById('value');
    const valueUnit = document.getElementById('value-unit');
    const maxDiscountGroup = document.getElementById('max-discount-group');
    const maxDiscountInput = document.getElementById('max_discount');

    function updateFormFields() {
        const type = typeSelect.value;
        
        if (type === 'PERCENTAGE') {
            valueUnit.textContent = '%';
            valueInput.max = 100;
            maxDiscountGroup.style.display = 'block';
            maxDiscountInput.required = true;
        } else {
            valueUnit.textContent = '₫';
            valueInput.max = '';
            maxDiscountGroup.style.display = 'none';
            maxDiscountInput.required = false;
        }
    }

    typeSelect.addEventListener('change', updateFormFields);
    updateFormFields(); // Initialize on page load
});
</script>
@endsection
