import HasRating, { emptyHasRating } from './has-rating';
import HasUserActions, { emptyUserActions } from './has-user-actions';
import User from './user';

export default interface Video extends HasRating, HasUserActions {
  id: string,
  name: string | null,
  description: string | null,
  createdAt: string | null,
  videoPath: string | null,
  snapshotTimestamp: number | null,
  sampleStartTimestamp: number | null,
  snapshotPath: string | null,
  tags: any[],
  permissions?: string[],
  status: string | null,
  createdBy: any[],
  owner: User | null,
}

export function emptyVideo(): Video {
  return {
    id: '',
    name: null,
    description: null,
    createdAt: null,
    videoPath: null,
    snapshotTimestamp: null,
    sampleStartTimestamp: null,
    snapshotPath: null,
    tags: [] as any[],
    permissions: [] as string[],
    status: '',
    createdBy: [] as any[],
    owner: null,
    ...emptyHasRating(),
    ...emptyUserActions(),
  };
}