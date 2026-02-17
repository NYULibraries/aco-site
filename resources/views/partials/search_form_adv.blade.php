<div class="search_holder_advanced widget" data-name="search_form">
    <form class="advanced" method="get" action="{{ url('search') }}" role="search">
        <div class="fieldset group1">
            <div class="select-hold">
                <div class="select-style">
                    <select name="scope" class="field-select" aria-label="Attribute">
                        <option value="q">Any Field / أي مُصطلح</option>
                        <option value="title">Title / العنوان</option>
                        <option value="author">Author / الكاتب</option>
                        <option value="category">Category / فئة الموضوع</option>
                        <option value="publisher">Publisher / الناشر</option>
                        <option value="pubplace">Place of Publication / مكان النشر</option>
                        <option value="provider">Provider / الشريك</option>
                        <option value="subject">Subject / الموضوع</option>
                    </select>
                </div>
            </div>
            <div class="select-hold">
                <div class="select-style">
                    <select class="scope-select" aria-label="Boolean Operator">
                        @foreach ($form['scopes'] as $item)
                            <option value="{{ $item['value'] }}" @if ($item['value'] === $form['scope']) selected @endif>
                                {{ $item['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="input-hold">
                <input @if(!empty($query)) value="{{ $query }}" @endif class="q1" aria-label="Search" name="q" type="text"
                    placeholder="search  /  ابحث" title="Enter the terms you wish to search for.">
            </div>
            <div class="submit-hold">
                <input type="submit" class="submit-search">
            </div>
        </div>
    </form>
</div>
