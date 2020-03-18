import { extend } from 'flarum/extend';
import app from 'flarum/app';
import PostControls from 'flarum/utils/PostControls';
import Button from 'flarum/components/Button';

import WarningModal from './components/WarningModal';

export default function () {
    extend(PostControls, 'moderationControls', function (items, post) {
        if (!app.session.user || !app.session.user.canManageWarnings()) return;

        items.add('warning',
            <Button
                icon="fas fa-exclamation-circle"
                onclick={() => app.modal.show(new WarningModal({
                    callback: () => {},
                    user: post.user(),
                    post: post,
                }))}>
                {app.translator.trans('askvortsov-moderator-warnings.forum.post_controls.warning_button')}
            </Button>
        );
    });
}