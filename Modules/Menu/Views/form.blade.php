@extends('admin.layouts.app')

@section('content')
    <div class="container-xxl">
        <form method="POST" action="{{ $isEdit ? route('admin.menus.update', $menu) : route('admin.menus.store') }}" id="menu-form">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <input type="hidden" name="items_payload" id="items-payload" value="{{ old('items_payload', $itemsPayload) }}">

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">{{ $isEdit ? 'Edit Menu' : 'Create Menu' }}</h4>
                            <p class="text-muted mb-0">Assign the menu to a frontend location and keep it active when ready.</p>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Menu Name</label>
                                <input type="text" name="name" id="menu-name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $menu->name) }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" id="menu-slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $menu->slug) }}">
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <select name="location" class="form-select @error('location') is-invalid @enderror">
                                    <option value="">Select location</option>
                                    @foreach ($locations as $value => $label)
                                        <option value="{{ $value }}" @selected(old('location', $menu->location) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $menu->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" value="1" name="is_active" id="menu-is-active" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu-is-active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Add Menu Item</h4>
                            <p class="text-muted mb-0">Link items to a page slug, custom URL, or a module route name or path.</p>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="editing-item-id">

                            <div class="mb-3">
                                <label class="form-label">Label</label>
                                <input type="text" id="item-title" class="form-control">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Link Type</label>
                                <select id="item-type" class="form-select">
                                    @foreach ($linkTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @unless($pageModuleAvailable)
                                    <div class="form-text">Page-based menu items are hidden because the Page module is missing, disabled, or its expected files do not match.</div>
                                @endunless
                            </div>

                            <div class="mb-3">
                                <label class="form-label" id="item-target-label">Target</label>
                                <input type="text" id="item-target" class="form-control" placeholder="about-us">
                                <select id="item-page-target" class="form-select d-none">
                                    <option value="">Select a page</option>
                                    @foreach ($pages as $page)
                                        <option value="{{ $page->slug }}">{{ $page->title }} (/{{ $page->slug }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="item-target-help">For pages, enter a slug or relative path like about-us.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">CSS Class</label>
                                <input type="text" id="item-css-class" class="form-control" placeholder="nav-item featured">
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="item-new-tab">
                                <label class="form-check-label" for="item-new-tab">Open in new tab</label>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-primary" id="save-item-button">Add Item</button>
                                <button type="button" class="btn btn-light" id="reset-item-button">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-1">Nested Menu Builder</h4>
                            <p class="text-muted mb-0">Drag items to reorder them. Drop on the middle of an item to nest it, or near the top or bottom to place it before or after.</p>
                        </div>
                        <div class="card-body">
                            @error('items_payload')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div id="menu-builder-empty" class="text-center text-muted py-5 border rounded">
                                Add your first menu item to start building the navigation tree.
                            </div>

                            <div id="menu-builder-tree" class="menu-builder-tree"></div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Menu' : 'Create Menu' }}</button>
                        <a href="{{ route('admin.menus.index') }}" class="btn btn-light">Back</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
<style>
    .menu-builder-tree ul {
        list-style: none;
        padding-left: 1.1rem;
        margin: 0;
        min-height: 1rem;
    }

    .menu-builder-tree > ul {
        padding-left: 0;
    }

    .menu-builder-list {
        border-radius: 0.75rem;
        transition: background-color 0.15s ease, outline-color 0.15s ease;
    }

    .menu-builder-list.is-drop-target {
        background: #f8fbff;
        outline: 1px dashed #93c5fd;
        outline-offset: 0.25rem;
    }

    .menu-builder-item {
        border: 1px solid #dfe4ea;
        border-radius: 0.75rem;
        background: #fff;
        margin-bottom: 0.75rem;
    }

    .menu-builder-item.is-dragging {
        opacity: 0.45;
    }

    .menu-builder-item.drop-before {
        border-top: 3px solid #3b82f6;
    }

    .menu-builder-item.drop-after {
        border-bottom: 3px solid #3b82f6;
    }

    .menu-builder-item.drop-inside > .menu-builder-item__content {
        background: #eff6ff;
    }

    .menu-builder-item__content {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem;
        align-items: flex-start;
    }

    .menu-builder-item__meta {
        min-width: 0;
        flex: 1 1 auto;
    }

    .menu-builder-item__title {
        font-weight: 600;
    }

    .menu-builder-item__details {
        color: #6c757d;
        font-size: 0.875rem;
        word-break: break-word;
    }

    .menu-builder-item__actions {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .menu-builder-item__children {
        padding: 0 1rem 1rem 1rem;
    }

    .menu-builder-child-dropzone {
        margin-top: 0.5rem;
        border: 1px dashed #cbd5e1;
        border-radius: 0.75rem;
        padding: 0.6rem 0.85rem;
        color: #64748b;
        font-size: 0.875rem;
        background: #f8fafc;
        transition: all 0.15s ease;
    }

    .menu-builder-child-dropzone.is-active {
        border-color: #60a5fa;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .menu-builder-item__drag {
        border: 0;
        background: #f1f5f9;
        color: #475569;
        border-radius: 0.6rem;
        width: 2.5rem;
        height: 2.5rem;
        flex: 0 0 auto;
        cursor: grab;
        font-size: 1rem;
        line-height: 1;
    }

    .menu-builder-item__drag:active {
        cursor: grabbing;
    }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const pageModuleAvailable = @json($pageModuleAvailable);
    const itemsPayloadInput = document.getElementById('items-payload');
    const treeContainer = document.getElementById('menu-builder-tree');
    const emptyState = document.getElementById('menu-builder-empty');
    const form = document.getElementById('menu-form');

    const nameInput = document.getElementById('menu-name');
    const slugInput = document.getElementById('menu-slug');

    const editingItemIdInput = document.getElementById('editing-item-id');
    const titleInput = document.getElementById('item-title');
    const typeInput = document.getElementById('item-type');
    const targetInput = document.getElementById('item-target');
    const pageTargetInput = document.getElementById('item-page-target');
    const targetLabel = document.getElementById('item-target-label');
    const targetHelp = document.getElementById('item-target-help');
    const cssClassInput = document.getElementById('item-css-class');
    const newTabInput = document.getElementById('item-new-tab');

    const saveButton = document.getElementById('save-item-button');
    const resetButton = document.getElementById('reset-item-button');

    const targetHelpText = {
        page: {
            label: 'Page',
            placeholder: 'about-us',
            help: 'Choose one of the created pages.',
        },
        custom: {
            label: 'Custom URL',
            placeholder: 'https://example.com/contact',
            help: 'Use a full URL, /path, #anchor, mailto:, or tel:.',
        },
        module: {
            label: 'Module Route / Path',
            placeholder: 'admin.gallery.index',
            help: 'Enter a named route if it exists, otherwise use a relative path or URL.',
        },
    };

    let menuItems = parseInitialItems();
    let draggedItemId = null;
    let draggedFromList = null;
    let draggedFromIndex = null;

    updateTargetFieldMeta();
    renderTree();

    typeInput.addEventListener('change', updateTargetFieldMeta);

    saveButton.addEventListener('click', () => {
        const payload = collectFormData();
        if (!payload) return;

        if (editingItemIdInput.value) {
            updateNode(menuItems, editingItemIdInput.value, payload);
        } else {
            menuItems.push(payload);
        }

        syncPayload();
        renderTree();
        resetEditor(); // 🔥 FIX
    });

    resetButton.addEventListener('click', resetEditor);
    form.addEventListener('submit', syncPayload);

    nameInput.addEventListener('input', () => {
        if (slugInput.dataset.touched === 'true') return;
        slugInput.value = slugify(nameInput.value);
    });

    slugInput.addEventListener('input', () => {
        slugInput.dataset.touched = slugInput.value.trim() !== '' ? 'true' : 'false';
    });

    function parseInitialItems() {
        try {
            const parsed = JSON.parse(itemsPayloadInput.value || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    function syncPayload() {
        itemsPayloadInput.value = JSON.stringify(menuItems);
    }

    function renderTree() {
        syncPayload();
        treeContainer.innerHTML = '';
        emptyState.style.display = menuItems.length ? 'none' : 'block';

        if (!menuItems.length) return;

        treeContainer.appendChild(renderBranch(menuItems));
    }

    function renderBranch(branch) {
        const list = document.createElement('ul');
        list.className = 'menu-builder-list';
        attachListDropEvents(list, branch);

        branch.forEach((item) => {
            const li = document.createElement('li');
            li.className = 'menu-builder-item';
            li.dataset.itemId = item.id;

            const clearDragState = () => {
                draggedItemId = null;
                draggedFromList = null;
                draggedFromIndex = null;
                clearDropClasses();
                li.classList.remove('is-dragging');
            };

            li.addEventListener('dragend', clearDragState);

            li.addEventListener('dragover', (event) => {
                event.preventDefault();
                const position = getDropPosition(li, event.clientY);
                clearDropClasses();
                li.classList.add(`drop-${position}`);
            });

            li.addEventListener('drop', (event) => {
                event.preventDefault();

                if (!draggedItemId || draggedItemId === item.id) return;

                moveItem(draggedItemId, item.id, getDropPosition(li, event.clientY));
                clearDropClasses();
            });

            const content = document.createElement('div');
            content.className = 'menu-builder-item__content';

            const dragHandle = document.createElement('button');
            dragHandle.type = 'button';
            dragHandle.className = 'menu-builder-item__drag';
            dragHandle.draggable = true;
            dragHandle.innerHTML = '&#8942;&#8942;';

            dragHandle.addEventListener('dragstart', (event) => {
                draggedItemId = item.id;

                const source = findNode(menuItems, item.id);
                draggedFromList = source?.parentList;
                draggedFromIndex = source?.index;

                event.dataTransfer.effectAllowed = 'move';
                li.classList.add('is-dragging');
            });

            dragHandle.addEventListener('dragend', clearDragState);

            const meta = document.createElement('div');
            meta.className = 'menu-builder-item__meta';
            meta.innerHTML = `
                <div class="menu-builder-item__title">${escapeHtml(item.title)}</div>
                <div class="menu-builder-item__details">${escapeHtml(formatItemDetails(item))}</div>
            `;

            const actions = document.createElement('div');
            actions.className = 'menu-builder-item__actions';

            const upBtn = document.createElement('button');
            upBtn.className = 'btn text-secondary btn-sm';
            // upBtn.textContent = 'Up';
            upBtn.innerHTML = '<iconify-icon icon="solar:alt-arrow-up-outline" width="16" height="16" />';

            const downBtn = document.createElement('button');
            downBtn.className = 'btn text-secondary btn-sm';
            downBtn.innerHTML = '<iconify-icon icon="solar:alt-arrow-down-outline" width="16" height="16" />';

            const indentBtn = document.createElement('button');
            indentBtn.className = 'btn text-secondary btn-sm';
            indentBtn.innerHTML = '<iconify-icon icon="solar:alt-arrow-right-linear" width="16" height="16" />';

            const outdentBtn = document.createElement('button');
            outdentBtn.className = 'btn text-secondary btn-sm';
            outdentBtn.innerHTML = '<iconify-icon icon="solar:alt-arrow-left-linear" width="16" height="16" />';

            const editBtn = document.createElement('button');
            editBtn.className = 'btn text-warning btn-sm';
            editBtn.innerHTML = '<iconify-icon icon="solar:pen-new-square-line-duotone" width="16" height="16" />';

            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'btn text-danger btn-sm';
            deleteBtn.innerHTML = '<iconify-icon icon="solar:trash-bin-trash-outline" width="16" height="16" />';

            upBtn.onclick = () => {
                moveNodeUp(item.id);
                renderTree();
            };

            downBtn.onclick = () => {
                moveNodeDown(item.id);
                renderTree();
            };

            indentBtn.onclick = () => {
                indentNode(item.id);
                renderTree();
            };

            outdentBtn.onclick = () => {
                outdentNode(item.id);
                renderTree();
            };

            editBtn.onclick = () => loadItemIntoEditor(item.id);
            deleteBtn.onclick = () => {
                removeNode(menuItems, item.id);
                renderTree();
                if (editingItemIdInput.value === String(item.id)) resetEditor();
            };

            actions.appendChild(upBtn);
            actions.appendChild(downBtn);
            actions.appendChild(indentBtn);
            actions.appendChild(outdentBtn);
            actions.appendChild(editBtn);
            actions.appendChild(deleteBtn);

            content.appendChild(dragHandle);
            content.appendChild(meta);
            content.appendChild(actions);
            li.appendChild(content);

            const childrenWrap = document.createElement('div');
            // childrenWrap.className = 'menu-builder-item__children';

            if (item.children?.length) {
                childrenWrap.appendChild(renderBranch(item.children));
            }

            // ✅ ENABLED DROPZONE
            const childDropzone = document.createElement('div');
            // childDropzone.className = 'menu-builder-child-dropzone';
            // childDropzone.textContent = 'Drop here to add sub-menu';

            childDropzone.addEventListener('dragover', (event) => {
                event.preventDefault();
                clearDropClasses();
                childDropzone.classList.add('is-active');
            });

            childDropzone.addEventListener('dragleave', () => {
                childDropzone.classList.remove('is-active');
            });

            childDropzone.addEventListener('drop', (event) => {
                event.preventDefault();

                if (!draggedItemId || draggedItemId === item.id) return;

                moveItemIntoChildren(draggedItemId, item.id);
                clearDropClasses();
            });

            childrenWrap.appendChild(childDropzone);
            li.appendChild(childrenWrap);

            list.appendChild(li);
        });

        return list;
    }

    function collectFormData() {
        const title = titleInput.value.trim();
        const target = typeInput.value === 'page'
            ? pageTargetInput.value.trim()
            : targetInput.value.trim();

        if (!title || !target) {
            alert('Each menu item needs both a label and a target.');
            return null;
        }

        const isEditing = !!editingItemIdInput.value;
        const existingNode = isEditing ? findNode(menuItems, editingItemIdInput.value)?.node : null;

        return {
            id: isEditing ? existingNode.id : createTemporaryId(),
            title,
            type: typeInput.value,
            target,
            css_class: cssClassInput.value.trim(),
            open_in_new_tab: newTabInput.checked,
            children: isEditing ? (existingNode?.children || []) : [],
        };
    }

    function loadItemIntoEditor(id) {
        const match = findNode(menuItems, id);
        if (!match) return;

        editingItemIdInput.value = String(match.node.id);
        titleInput.value = match.node.title;
        typeInput.value = match.node.type;
        ensurePageOption(match.node.target);
        pageTargetInput.value = match.node.target;
        targetInput.value = match.node.target;
        cssClassInput.value = match.node.css_class || '';
        newTabInput.checked = !!match.node.open_in_new_tab;

        saveButton.textContent = 'Update Item';
        updateTargetFieldMeta();
    }

    function resetEditor() {
        editingItemIdInput.value = '';
        titleInput.value = '';
        targetInput.value = '';
        cssClassInput.value = '';
        newTabInput.checked = false;
        typeInput.value = pageModuleAvailable ? 'page' : 'custom';
        pageTargetInput.value = '';

        saveButton.textContent = 'Add Item';
        updateTargetFieldMeta();
    }

    function updateTargetFieldMeta() {
        const config = targetHelpText[typeInput.value] || targetHelpText.custom;
        targetLabel.textContent = config.label;
        targetInput.placeholder = config.placeholder;
        targetHelp.textContent = config.help;
        const isPageType = typeInput.value === 'page';
        targetInput.classList.toggle('d-none', isPageType);
        pageTargetInput.classList.toggle('d-none', !isPageType);
    }

    function ensurePageOption(value) {
        if (!value || Array.from(pageTargetInput.options).some(option => option.value === value)) {
            return;
        }

        const option = new Option(value, value, true, true);
        pageTargetInput.appendChild(option);
    }

    function moveItem(sourceId, targetId, position) {
        const detached = detachNode(menuItems, sourceId);
        const source = detached?.node;
        if (!source) return;

        if (containsNode(source, targetId)) {
            restoreDetachedNode(detached);
            return;
        }

        const target = findNode(menuItems, targetId);
        if (!target) return;

        if (position === 'inside') {
            target.node.children = target.node.children || [];
            target.node.children.push(source);
        } else {
            const index = position === 'before' ? target.index : target.index + 1;
            target.parentList.splice(index, 0, source);
        }

        renderTree();
    }

    function moveItemIntoChildren(sourceId, parentId) {
        if (String(sourceId) === String(parentId)) return;

        const detached = detachNode(menuItems, sourceId);
        const source = detached?.node;
        if (!source) return;

        const parent = findNode(menuItems, parentId);
        if (!parent) return;

        parent.node.children = parent.node.children || [];
        parent.node.children.push(source);

        renderTree();
    }

    function detachNode(branch, id) {
        for (let i = 0; i < branch.length; i++) {
            if (String(branch[i].id) === String(id)) {
                return { node: branch.splice(i, 1)[0], parentList: branch, index: i };
            }
            const child = detachNode(branch[i].children || [], id);
            if (child) return child;
        }
        return null;
    }

    function restoreDetachedNode(detached) {
        if (!detached) return;
        detached.parentList.splice(detached.index, 0, detached.node);
    }

    function removeNode(branch, id) {
        return !!detachNode(branch, id);
    }

    function moveNodeUp(id) {
        const match = findNode(menuItems, id);
        if (!match || match.index === 0) return false;

        const swapIndex = match.index - 1;
        [match.parentList[swapIndex], match.parentList[match.index]] = [match.parentList[match.index], match.parentList[swapIndex]];
        syncPayload();

        return true;
    }

    function moveNodeDown(id) {
        const match = findNode(menuItems, id);
        if (!match || match.index >= match.parentList.length - 1) return false;

        const swapIndex = match.index + 1;
        [match.parentList[swapIndex], match.parentList[match.index]] = [match.parentList[match.index], match.parentList[swapIndex]];
        syncPayload();

        return true;
    }

    function indentNode(id) {
        const match = findNode(menuItems, id);
        if (!match || match.index === 0) return false;

        const previousSibling = match.parentList[match.index - 1];
        const detached = detachNode(menuItems, id);

        if (!previousSibling || !detached) {
            restoreDetachedNode(detached);
            return false;
        }

        previousSibling.children = previousSibling.children || [];
        previousSibling.children.push(detached.node);
        syncPayload();

        return true;
    }

    function outdentNode(id) {
        const match = findNode(menuItems, id);
        if (!match || !match.parentNode) return false;

        const parentMatch = findNode(menuItems, match.parentNode.id);
        const detached = detachNode(menuItems, id);

        if (!parentMatch || !detached) {
            restoreDetachedNode(detached);
            return false;
        }

        parentMatch.parentList.splice(parentMatch.index + 1, 0, detached.node);
        syncPayload();

        return true;
    }

    function updateNode(branch, id, replacement) {
        const match = findNode(branch, id);
        if (!match) return;

        match.parentList[match.index] = {
            ...match.node,
            ...replacement,
            id: match.node.id,
            children: match.node.children
        };
    }

    function findNode(branch, id, parentNode = null) {
        for (let i = 0; i < branch.length; i++) {
            if (String(branch[i].id) === String(id)) {
                return { node: branch[i], parentList: branch, index: i, parentNode };
            }
            const found = findNode(branch[i].children || [], id, branch[i]);
            if (found) return found;
        }
        return null;
    }

    function containsNode(node, id) {
        if (String(node.id) === String(id)) return true;
        return (node.children || []).some(c => containsNode(c, id));
    }

    function clearDropClasses() {
        document.querySelectorAll('.menu-builder-item').forEach(el => {
            el.classList.remove('drop-before', 'drop-after', 'drop-inside');
        });
        document.querySelectorAll('.menu-builder-child-dropzone').forEach(el => {
            el.classList.remove('is-active');
        });
    }

    function attachListDropEvents(list, branch) {
        list.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!e.target.closest('.menu-builder-item')) {
                list.classList.add('is-drop-target');
            }
        });

        list.addEventListener('drop', (e) => {
            e.preventDefault();
            if (!draggedItemId) return;
            const detached = detachNode(menuItems, draggedItemId);
            if (detached?.node) branch.push(detached.node);
            renderTree();
        });
    }

    function getDropPosition(el, y) {
        const rect = el.getBoundingClientRect();
        const offset = y - rect.top;
        const third = rect.height / 3;
        if (offset < third) return 'before';
        if (offset > third * 2) return 'after';
        return 'inside';
    }

    function formatItemDetails(item) {
        return `${item.type} | ${item.target}${item.open_in_new_tab ? ' | New tab' : ''}`;
    }

    function createTemporaryId() {
        return crypto.randomUUID ? crypto.randomUUID() :
            `tmp-${Date.now()}-${Math.random().toString(16).slice(2, 8)}`;
    }

    function slugify(value) {
        return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

})();
</script>
@endpush
