export const emailFormControl = {
  name: 'email', 
  label: ['form.common.email.label', {}], 
  type: 'email', 
  placeholder: ['form.common.email.placeholder', {}],
}

export const passwordFormControl = {
  name: 'password', 
  label: ['form.common.password.label', {}], 
  type: 'password', 
  placeholder: ['form.common.password.placeholder', {}],
}

export const repeatPasswordFirstFormControl = {
  name: 'password[first]', 
  label: ['form.common.password.label', {}], 
  type: 'password', 
  placeholder: ['form.common.password.placeholder', {}],
}

export const repeatPasswordSecondFormControl = {
  name: 'password[second]', 
  label: ['form.common.repeatPassword.label', {}], 
  type: 'password', 
  placeholder: ['form.common.repeatPassword.placeholder', {}],
}

export const nickFormControl = {
  name: 'nick', 
  label: ['form.common.nick.label', {}], 
  type: 'text', 
  placeholder: ['form.common.nick.placeholder', {}],
}

export const birthDateFormControl = {
  name: 'birthDate', 
  label: ['form.common.birth.label', {}], 
  type: 'date', 
  placeholder: ['form.common.birth.placeholder', {}],
}

export const userImageFormControl = {
  name: 'image', 
  label: ['form.common.userImage.label', {}], 
  type: 'file', 
  inputAttributes: { accept: 'image/*' },
}

export const userBackgroundFormControl = {
  name: 'background', 
  label: ['form.common.userBackground.label', {}], 
  type: 'file', 
  inputAttributes: { accept: 'image/*' },
}

export const appName: string = 'Social';

export const imagesPath: string = '/images';

export const videosPath: string = '/videos';

export const commentsPerLoad = 3;

export type Color = 'transparent' | 'dark' | 'primary' | 'secondary' | 'light' | 'danger' | 'info' | 'success';

export function setTitle(title: string) {
  document.querySelector('')
}

export function getElementByIdOrThrowError<T>(id: string): T {
  let element = <T> document.getElementById(id);
  if (!element) {
    throw `Missing element with id '${id}'.`;
  }
  return element;
}

export function getCookieValue(cookieName: string): string | null {
  let match: any[] | null = document.cookie.match(new RegExp('(^| )' + cookieName + '=([^;]+)'));
  
  return match ? match[2] : null;
}

export type Stringable = string | [string, { [key: string]: string }];

export function relativeDateTime(dateTime: string | null): Stringable {
  if (dateTime === null) {
    return ['common.now', {}];
  }
  let now = new Date();
  let date = new Date(dateTime);
  let diff = (now.getTime() - date.getTime()) / 1000;

  if (diff < 60) {
    return ['common.momentAge', {}];
  } else if (diff < 3600) {
    return ['common.minutesAgo', { minutes: Math.trunc(diff / 60) as unknown as string }];
  } else if (diff < 3600 * 24) {
    return ['common.hoursAgo', { hours: Math.trunc(diff / 3600) as unknown as string }];
  } else if (diff < 3600 * 24 * 7) {
    return ['common.daysAgo', { days: Math.trunc(diff / (3600 * 24)) as unknown as string }];
  } else if (diff < 3600 * 24 * 30) {
    return ['common.weeksAgo', { weeks: Math.trunc(diff / (3600 * 24 * 7)) as unknown as string }];
  } else if (diff < 3600 * 24 * 365) {
    return ['common.monthsAgo', { months: Math.trunc(diff / (3600 * 24 * 7 * 30)) as unknown as string }];
  } else {
    return ['common.yearsAgo', { years: Math.trunc(diff / (3600 * 24 * 7 * 365)) as unknown as string }];
  }
}