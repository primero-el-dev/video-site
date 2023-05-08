import HasRating, { emptyHasRating } from './has-rating';
import HasUserActions, { emptyUserActions } from './has-user-actions';
import User, { emptyUser } from './user';

export default interface Comment extends HasRating, HasUserActions {
  id: number,
  content: string,
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
    id: 0,
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