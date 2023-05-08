export default interface HasRating {
  likesCount: number,
  dislikesCount: number,
  currentUserRating: number | null,
}

export function emptyHasRating(): HasRating {
  return {
    likesCount: 0,
    dislikesCount: 0,
    currentUserRating: null,
  }
}