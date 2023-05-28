import User from './user';
import Video from './video';
import Comment from './comment';

export default interface Notification {
  id: number,
  action: string,
  count: number,
  seen: boolean,
  user: User,
  role: string | null,
  subject: Comment | Video | User,
  subjectType: string,
  updatedAt: string,
  userSubjectActions: any[],
}