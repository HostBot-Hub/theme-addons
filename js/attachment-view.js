import { useSelect } from '@wordpress/data';

const AttachmentEditView = ({ className }) => {
	// Get the current post (should be an attachment)
	const currentPost = useSelect((select) => select('core/editor').getCurrentPost(), []);
	const title = currentPost?.title ?? 'Attachment';
	const mimeType = currentPost?.mime_type ?? null;

	return (
		<div className={`${className} attachment-wrapper`}>
			<p>
				<strong>File Title:</strong> {title}
			</p>
			{mimeType && (
				<p>
					<strong>MIME Type:</strong> {mimeType}
				</p>
			)}
			<p>(This is a dynamic attachment view block.)</p>
		</div>
	);
};
export default AttachmentEditView;