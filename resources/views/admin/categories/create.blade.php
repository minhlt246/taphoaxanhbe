@extends('layouts.admin')

@section('title', 'Thêm danh mục - Tạp Hóa Xanh Admin')

@section('content')
                <!-- Top Navigation -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Thêm danh mục mới</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <!-- Create Category Form -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin danh mục</h6>
                    </div>
                    <div class="card-body">
                        <form id="create-category-form">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="parent_id" class="form-label">Danh mục cha</label>
                                        <select class="form-control" id="parent_id" name="parent_id">
                                            <option value="">Chọn danh mục cha (tùy chọn)</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL hình ảnh</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://example.com/image.jpg">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Hủy</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Tạo danh mục
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
@endsection

@push('scripts')
<script>
        // Fetch parent categories for dropdown
        async function fetchParentCategories() {
            try {
                const response = await fetch('/api/categories');
                const data = await response.json();
                
                const select = document.getElementById('parent_id');
                if (data.data && data.data.length > 0) {
                    data.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error fetching parent categories:', error);
            }
        }

        // Handle form submission
        document.getElementById('create-category-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Clear previous validation errors
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            
            try {
                const response = await fetch('/api/categories', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    alert(result.message);
                    window.location.href = '/admin/categories';
                } else {
                    // Handle validation errors
                    if (result.errors) {
                        Object.keys(result.errors).forEach(field => {
                            const input = document.querySelector(`[name="${field}"]`);
                            const feedback = input.nextElementSibling;
                            input.classList.add('is-invalid');
                            feedback.textContent = result.errors[field][0];
                        });
                    } else {
                        alert(result.message || 'Có lỗi xảy ra khi tạo danh mục');
                    }
                }
            } catch (error) {
                console.error('Error creating category:', error);
                alert('Có lỗi xảy ra khi tạo danh mục');
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            fetchParentCategories();
        });
</script>
@endpush
