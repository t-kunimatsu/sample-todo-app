import { CardType, ColumnType, DialogMode } from "@/types/todo";
import { DragEndEvent, DragOverEvent } from "@dnd-kit/core";
import { arrayMove } from "@dnd-kit/sortable";
import { create } from "zustand";

type BoardState = {
  columns: ColumnType[];
  setColumns: (columns: ColumnType[]) => void;
  addCard: (columnId: string, newCard: CardType) => void;
  editCard: (editCard: CardType) => void;
  handleDragOver: (event: DragOverEvent) => void;
  handleDragEnd: (event: DragEndEvent) => void;
  currentColumnId: string;
  setCurrentColumnId: (id: string) => void;
  currentCard: CardType;
  setCurrentCard: (card: CardType) => void;
  dialogOpen: boolean;
  setDialogOpen: (open: boolean) => void;
  dialogMode: DialogMode;
  setDialogMode: (mode: DialogMode) => void;
};

export const useTodoBoard = create<BoardState>((set, get) => {
  const findColumn = (unique: string | null) => {
    const state = get();
    if (!unique) return null;
    if (state.columns.some((column) => column.id === unique)) {
      return state.columns.find((column) => column.id === unique) ?? null;
    }
    const id = String(unique);
    const itemWithColumnId = state.columns.flatMap((column) => {
      const columnId = column.id;
      return column.cards.map((card) => ({ itemId: card.id, columnId: columnId }));
    });
    const columnId = itemWithColumnId.find((item) => item.itemId === id)?.columnId;
    return state.columns.find((column) => column.id === columnId) ?? null;
  };

  const updateCardPosition = (columnId: string, fromIndex: number, toIndex: number) => {
    set((state) => {
      const column = state.columns.find((column) => column.id === columnId);
      if (!column) return state;

      const updatedCards = arrayMove(column.cards, fromIndex, toIndex);
      return {
        columns: state.columns.map((column) =>
          column.id === columnId ? { ...column, cards: updatedCards } : column
        ),
      };
    });
  };

  const addCard = (columnId: string, newCard: CardType) => {
    set((state) => {
      const column = state.columns.find((column) => column.id === columnId);
      if (!column) return state;
      return {
        columns: state.columns.map((column) =>
          column.id === columnId ? { ...column, cards: [...column.cards, newCard] } : column
        ),
      };
    });
  };

  const editCard = (editCard: CardType) => {
    set((state) => {
      return {
        columns: state.columns.map((column) => {
          return {
            ...column,
            cards: column.cards.map((card) =>
              card.id === editCard.id ? { ...card, title: editCard.title } : card
            ),
          };
        }),
      };
    });
  };

  const handleDragOver = (event: DragOverEvent) => {
    const { active, over, delta } = event;
    const activeId = String(active.id);
    const overId = over ? String(over.id) : null;
    const activeColumn = findColumn(activeId);
    const overColumn = findColumn(overId);
    if (!activeColumn || !overColumn || activeColumn === overColumn) {
      return null;
    }
    set((state) => {
      const activeItems = activeColumn.cards;
      const overItems = overColumn.cards;
      const activeIndex = activeItems.findIndex((i) => i.id === activeId);
      const overIndex = overItems.findIndex((i) => i.id === overId);
      const newIndex = () => {
        const putOnBelowLastItem = overIndex === overItems.length - 1 && delta.y > 0;
        const modifier = putOnBelowLastItem ? 1 : 0;
        return overIndex >= 0 ? overIndex + modifier : overItems.length + 1;
      };
      return {
        columns: state.columns.map((column) => {
          if (column.id === activeColumn.id) {
            column.cards = activeItems.filter((item) => item.id !== activeId);
            return column;
          }
          if (column.id === overColumn.id) {
            column.cards = [
              ...overItems.slice(0, newIndex()),
              activeItems[activeIndex],
              ...overItems.slice(newIndex(), overItems.length),
            ];
            return column;
          }
          return column;
        }),
      };
    });
  };

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    const activeId = String(active.id);
    const overId = over ? String(over.id) : null;
    const activeColumn = findColumn(activeId);
    const overColumn = findColumn(overId);
    if (!activeColumn || !overColumn || activeColumn !== overColumn) {
      return null;
    }
    const activeIndex = activeColumn.cards.findIndex((card) => card.id === activeId);
    const overIndex = overColumn.cards.findIndex((card) => card.id === overId);
    if (activeIndex !== overIndex) {
      updateCardPosition(activeColumn.id, activeIndex, overIndex);
    }
  };

  return {
    columns: [
      {
        id: "todo",
        title: "ToDo",
        cards: [
          {
            id: "Card1",
            title: "Card1",
          },
          {
            id: "Card2",
            title: "Card2",
          },
        ],
        showAddTask: true,
        showEditTask: true,
      },
      {
        id: "doing",
        title: "Doing",
        cards: [
          {
            id: "Card3",
            title: "Card3",
          },
          {
            id: "Card4",
            title: "Card4",
          },
        ],
        showAddTask: true,
        showEditTask: true,
      },
      {
        id: "done",
        title: "Done",
        cards: [],
      },
    ],
    setColumns: (columns) => set({ columns }),
    addCard,
    editCard,
    handleDragOver,
    handleDragEnd,
    currentColumnId: "",
    setCurrentColumnId: (id) => set({ currentColumnId: id }),
    currentCard: { id: "", title: "" },
    setCurrentCard: (card) => set({ currentCard: card }),
    dialogOpen: false,
    setDialogOpen: (open) => set({ dialogOpen: open }),
    dialogMode: "add",
    setDialogMode: (mode) => set({ dialogMode: mode }),
  };
});
