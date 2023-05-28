export default interface User {
  id: string,
  email?: string,
  nick: string,
  birthDate?: string,
  createdAt: string,
  roles?: string[],
  imagePath: string | null,
  backgroundPath?: string | null,
  verified?: boolean,
  deletedAt?: string | null,
}

export function emptyUser(): User {
  return {
    id: '',
    nick: '',
    imagePath: null,
    createdAt: '',
  }
}