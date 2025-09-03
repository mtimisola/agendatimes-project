@extends('author.layout.app')

@section('heading', 'Edit Post')

@section('button')
<a href="{{ route('author_post_show') }}" class="btn btn-primary"><i class="fas fa-eye"></i> View</a>
@endsection

@section('main_content')
<div class="section-body">
    <form action="{{ route('author_post_update',$post_single->id) }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        {{-- Post Title --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Post Title *</label>
                            <input type="text" class="form-control form-control-lg" name="post_title" value="{{ $post_single->post_title }}" placeholder="Enter post title">
                        </div>
                        
                        {{-- Post Detail --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Post Detail *</label>
                            <textarea name="post_detail" class="form-control snote" cols="30" rows="10" placeholder="Write your post content here">{{ $post_single->post_detail }}</textarea>
                        </div>
                        
                        {{-- Existing Post Photo --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Existing Post Photo</label>
                            <div class="existing-photo-container mb-3">
                                <img src="{{ asset('uploads/'.$post_single->post_photo) }}" alt="Current post photo" class="img-thumbnail" style="max-width:300px;">
                            </div>
                        </div>
                        
                        {{-- Change Post Photo --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Change Post Photo</label>
                            <div class="file-upload-wrapper mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-upload"></i></span>
                                    <input type="file" class="form-control" name="post_photo" id="postPhotoUpload">
                                </div>
                                <div class="form-text">Accepted formats: JPG, PNG, GIF. Max size: 5MB</div>
                            </div>

                            {{-- Gallery: select an existing photo instead of uploading --}}
                            @if(isset($photos) && $photos->count())
                                <div class="photo-selection-container">
                                    <p class="text-muted mb-3">Or select an existing image from the gallery below:</p>

                                    {{-- Selected photo preview --}}
                                    <div class="selected-photo-preview card mb-4" id="selectedPhotoPreview" style="display:{{ $post_single->photo_id ? 'block' : 'none' }};">
                                        <div class="card-body">
                                            <h6 class="card-title">Selected Photo</h6>
                                            <div class="d-flex align-items-center">
                                                <div class="preview-image me-3">
                                                    <img src="" alt="Selected Photo" id="selectedPhotoImg" class="img-thumbnail" style="width:120px; height:80px; object-fit:cover;">
                                                </div>
                                                <div class="preview-info">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="changePhotoBtn">Change Selection</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Quick 12 thumbnails --}}
                                    <div class="gallery-section">
                                        <h6 class="mb-3">Quick Selection</h6>
                                        <div class="row g-2" id="quick-thumbnails">
                                            @foreach($photos->take(12) as $photo)
                                                <div class="col-6 col-md-3 col-lg-2">
                                                    <div class="gallery-thumb card h-100 {{ $post_single->post_photo == $photo->photo || (isset($post_single->photo_id) && $post_single->photo_id == $photo->id) ? 'selected' : '' }}" data-id="{{ $photo->id }}" data-caption="{{ $photo->caption }}">
                                                        <img src="{{ asset('uploads/'.$photo->photo) }}" 
                                                             alt="{{ $photo->caption }}" 
                                                             class="card-img-top" 
                                                             style="height: 100px; object-fit: cover;">
                                                        <div class="card-body p-2 text-center">
                                                            <small class="d-block text-truncate" title="{{ $photo->caption }}">{{ $photo->caption }}</small>
                                                            <div class="form-check mt-2">
                                                                <input class="form-check-input" type="radio" name="photo_id" value="{{ $photo->id }}" 
                                                                       id="photo_{{ $photo->id }}" {{ $post_single->post_photo == $photo->photo || (isset($post_single->photo_id) && $post_single->photo_id == $photo->id) ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="photo_{{ $photo->id }}">Select</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        {{-- Open Full Gallery Button --}}
                                        <div class="text-center mt-4">
                                            <button type="button" id="openFullGallery" class="btn btn-outline-primary">
                                                <i class="fas fa-images me-2"></i> Browse Full Gallery
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Full Gallery Modal --}}
                                    <div class="modal fade" id="fullGalleryModal" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Select a Photo</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{-- Search --}}
                                                    <div class="input-group mb-4">
                                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                                        <input type="text" id="modalPhotoSearch" placeholder="Search by caption..." class="form-control">
                                                    </div>

                                                    {{-- Full gallery thumbnails --}}
                                                    <div class="row g-3" id="modal-gallery">
                                                        @foreach($photos as $photo)
                                                            <div class="col-6 col-md-3 col-lg-2 modal-photo-item">
                                                                <div class="gallery-thumb card h-100 {{ $post_single->post_photo == $photo->photo || (isset($post_single->photo_id) && $post_single->photo_id == $photo->id) ? 'selected' : '' }}" data-id="{{ $photo->id }}" data-caption="{{ $photo->caption }}">
                                                                    <img src="{{ asset('uploads/'.$photo->photo) }}" 
                                                                         alt="{{ $photo->caption }}" 
                                                                         class="card-img-top" 
                                                                         style="height: 100px; object-fit: cover;">
                                                                    <div class="card-body p-2 text-center">
                                                                        <small class="d-block text-truncate" title="{{ $photo->caption }}">{{ $photo->caption }}</small>
                                                                        <div class="form-check mt-2">
                                                                            <input class="form-check-input" type="radio" name="photo_id" 
                                                                                   value="{{ $photo->id }}" id="modal_photo_{{ $photo->id }}" 
                                                                                   {{ $post_single->post_photo == $photo->photo || (isset($post_single->photo_id) && $post_single->photo_id == $photo->id) ? 'checked' : '' }}>
                                                                            <label class="form-check-label small" for="modal_photo_{{ $photo->id }}">Select</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="button" class="btn btn-primary" id="confirmSelection">Confirm Selection</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        {{-- Category --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Select Category *</label>
                            <select name="sub_category_id" class="form-control select2">
                                @foreach($sub_categories as $item)
                                <option value="{{ $item->id }}" @if($item->id == $post_single->sub_category_id) selected @endif>{{ $item->sub_category_name }} ({{ $item->rCategory->category_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="row">
                            {{-- Share --}}
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label">Is Sharable?</label>
                                    <select name="is_share" class="form-control">
                                        <option value="1" @if($post_single->is_share == 1) selected @endif>Yes</option>
                                        <option value="0" @if($post_single->is_share == 0) selected @endif>No</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Comment --}}
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="form-label">Allow Comment?</label>
                                    <select name="is_comment" class="form-control">
                                        <option value="1" @if($post_single->is_comment == 1) selected @endif>Yes</option>
                                        <option value="0" @if($post_single->is_comment == 0) selected @endif>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Existing Tags --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Existing Tags</label>
                            @if($existing_tags->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Tag Name</th>
                                                <th width="100">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($existing_tags as $item)
                                                <tr>
                                                    <td>{{ $item->tag_name }}</td>
                                                    <td class="text-center">
                                                        <a href="{{ route('author_post_delete_tag', [$item->id,$post_single->id]) }}" class="btn btn-sm btn-danger" onClick="return confirm('Are you sure?');">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">No tags added yet.</div>
                            @endif
                        </div>
                        
                        {{-- New Tags --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Add New Tags</label>
                            <input type="text" class="form-control" name="tags" value="" placeholder="Enter new tags separated by commas">
                            <div class="form-text">Separate tags with commas (e.g., technology, web, design)</div>
                        </div>
                        
                        {{-- Language --}}
                        <div class="form-group mb-4">
                            <label class="form-label">Select Language</label>
                            <select name="language_id" class="form-control">
                                @foreach($global_language_data as $row)
                                <option value="{{ $row->id }}" @if($row->id == $post_single->language_id) selected @endif>{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Update Post</button>
                        <a href="{{ route('author_post_show') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #2c3e50;
    }
    
    .gallery-thumb {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .gallery-thumb:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .gallery-thumb.selected {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.3);
    }
    
    .selected-photo-preview {
        border-left: 4px solid #3498db;
    }
    
    .file-upload-wrapper {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    
    .file-upload-wrapper:hover {
        border-color: #3498db;
        background-color: #e8f4ff;
    }
    
    #fullGalleryModal .modal-content {
        border-radius: 12px;
        overflow: hidden;
    }
    
    #fullGalleryModal .modal-header {
        background-color: #2c3e50;
        color: white;
    }
    
    .no-results {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
        font-style: italic;
    }
    
    .existing-photo-container {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        display: inline-block;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fullGalleryModal = new bootstrap.Modal(document.getElementById('fullGalleryModal'));
    const selectedPhotoPreview = document.getElementById('selectedPhotoPreview');
    const selectedPhotoImg = document.getElementById('selectedPhotoImg');
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const postPhotoUpload = document.getElementById('postPhotoUpload');
    const confirmSelectionBtn = document.getElementById('confirmSelection');
    
    // Open modal
    if (document.getElementById('openFullGallery')) {
        document.getElementById('openFullGallery').addEventListener('click', function() {
            fullGalleryModal.show();
        });
    }
    
    // Update preview when a photo is selected
    function updatePhotoPreview(input) {
        if (selectedPhotoPreview && selectedPhotoImg) {
            selectedPhotoPreview.style.display = 'block';
            const img = input.closest('.gallery-thumb').querySelector('img');
            selectedPhotoImg.src = img.src;
            
            // Add selected class to parent
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('selected');
            });
            input.closest('.gallery-thumb').classList.add('selected');
        }
    }
    
    // Handle radio button changes
    document.querySelectorAll('input[name="photo_id"]').forEach(input => {
        input.addEventListener('change', function() {
            updatePhotoPreview(this);
        });
    });
    
    // Change photo button
    if (changePhotoBtn) {
        changePhotoBtn.addEventListener('click', function() {
            fullGalleryModal.show();
        });
    }
    
    // Confirm selection button
    if (confirmSelectionBtn) {
        confirmSelectionBtn.addEventListener('click', function() {
            fullGalleryModal.hide();
        });
    }
    
    // Search functionality inside modal - now searches by caption
    if (document.getElementById('modalPhotoSearch')) {
        document.getElementById('modalPhotoSearch').addEventListener('keyup', function() {
            let query = this.value.toLowerCase();
            let hasResults = false;
            
            document.querySelectorAll('#modal-gallery .modal-photo-item').forEach(item => {
                const caption = item.querySelector('.gallery-thumb').getAttribute('data-caption').toLowerCase();
                if (caption.includes(query)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // Show no results message if needed
            let noResultsElem = document.getElementById('noResultsMessage');
            if (!hasResults) {
                if (!noResultsElem) {
                    noResultsElem = document.createElement('div');
                    noResultsElem.id = 'noResultsMessage';
                    noResultsElem.className = 'no-results';
                    noResultsElem.textContent = 'No images found matching your search.';
                    document.getElementById('modal-gallery').appendChild(noResultsElem);
                }
            } else if (noResultsElem) {
                noResultsElem.remove();
            }
        });
    }
    
    // File upload change
    if (postPhotoUpload) {
        postPhotoUpload.addEventListener('change', function() {
            // Clear any selected photo from gallery
            document.querySelectorAll('input[name="photo_id"]').forEach(radio => {
                radio.checked = false;
            });
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('selected');
            });
            if (selectedPhotoPreview) {
                selectedPhotoPreview.style.display = 'none';
            }
        });
    }
    
    // Click on thumbnail to select
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', function(e) {
            if (!e.target.closest('.form-check')) {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            }
        });
    });
    
    // Initialize any existing selection
    const initialSelected = document.querySelector('input[name="photo_id"]:checked');
    if (initialSelected && selectedPhotoImg) {
        updatePhotoPreview(initialSelected);
        const img = initialSelected.closest('.gallery-thumb').querySelector('img');
        selectedPhotoImg.src = img.src;
    }
});
</script>
@endsection