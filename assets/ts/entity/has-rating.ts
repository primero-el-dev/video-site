export default interface HasRating {
  likesCount: number,
  dislikesCount: number,
  currentUserRating: string | null,
}

export function emptyHasRating(): HasRating {
  return {
    likesCount: 0,
    dislikesCount: 0,
    currentUserRating: null,
  }
}