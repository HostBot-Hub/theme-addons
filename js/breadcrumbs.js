import { RichText } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

const BreadcrumbsEditView = ({ attributes, setAttributes, className }) => {
	// Get current post
	const currentPost = useSelect((select) => select('core/editor').getCurrentPost(), []);
	const pageTitle = currentPost?.title ?? document.title;

	// Get parent page (for pages)
	const parentPost = useSelect(
		(select) =>
			currentPost?.parent
				? select('core').getEntityRecord('postType', 'page', currentPost.parent)
				: null,
		[currentPost?.parent]
	);

	// Get first category term (for posts)
	const firstCategoryTerm = useSelect(
		(select) =>
			currentPost?.categories?.length
				? select('core').getEntityRecord('taxonomy', 'category', currentPost.categories[0])
				: null,
		[currentPost?.categories?.[0]]
	);

	// Build breadcrumb trail
	const breadcrumbParts = ['Home'];

	if (firstCategoryTerm?.name) {
		breadcrumbParts.push(firstCategoryTerm.name);
	} else if (parentPost?.title?.rendered) {
		breadcrumbParts.push(parentPost.title.rendered);
	}

	if (pageTitle) {
		breadcrumbParts.push(pageTitle);
	}

	const preview = breadcrumbParts.join(' > ');

	// Support future editable breadcrumbText
	const onChangeBreadcrumbText = (newText) => {
		setAttributes({ breadcrumbText: newText });
	};

	return (
		<div className={className}>
			<RichText
				tagName="p"
				value={attributes.breadcrumbText || preview}
				onChange={onChangeBreadcrumbText}
				placeholder="Enter your breadcrumb text here..."
			/>
		</div>
	);
};

export default BreadcrumbsEditView;
