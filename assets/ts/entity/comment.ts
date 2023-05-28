import HasRating, { emptyHasRating } from './has-rating';
import HasUserActions, { emptyUserActions } from './has-user-actions';
import User, { emptyUser } from './user';
import Video from './video';

export default interface Comment extends HasRating, HasUserActions {
  id: string,
  order: number,
  content: string,
  video?: Video,
  children?: Comment[],
  childrenCount: number,
  permissions: string[],
  createdAt: string,
  updatedAt: string | null,
  deleted: boolean,
  owner: User,
}

export function emptyComment(): Comment {
  return {
    id: '',
    order: 0,
    content: '',
    children: [] as Comment[],
    childrenCount: 0,
    permissions: [] as string[],
    createdAt: '',
    updatedAt: null,
    deleted: false,
    owner: emptyUser(),
    ...emptyHasRating(),
    ...emptyUserActions(),
  }
}