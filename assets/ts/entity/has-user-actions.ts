export interface UserAction {
  action: string, 
  additionalInfo: { [key: string]: string },
}

export default interface HasUserActions {
  currentUserActions?: UserAction[],
}

export function emptyUserActions(): HasUserActions {
  return {
    currentUserActions: [] as UserAction[],
  }
}

export function addUserAction(entity: HasUserActions, userAction: UserAction): void {
  if (entity.currentUserActions === undefined) {
    entity.currentUserActions = [] as UserAction[];
  }
  entity.currentUserActions.push(userAction);
}

export function deleteUserAction(entity: HasUserActions, action: string): void {
  entity.currentUserActions = entity.currentUserActions?.filter(a => a.action !== action);
}