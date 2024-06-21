export type CardType = {
  id: string;
  title: string;
};

export type ColumnType = {
  id: string;
  title: string;
  cards: CardType[];
  showAddTask?: boolean;
  showEditTask?: boolean;
};

export type DialogMode = "add" | "edit";
