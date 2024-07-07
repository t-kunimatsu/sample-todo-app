import { Task, Tasks, Status, postTask, NewTask, patchTask } from "@/Apis/tasks";
import { DragEndEvent, DragOverEvent } from "@dnd-kit/core";
import { arrayMove } from "@dnd-kit/sortable";
import { create } from "zustand";
import { ColumnProps } from "./Column";

export type DialogMode = "add" | "edit";

type BoardState = {
  columns: ColumnProps[];
  setColumns: (columns: ColumnProps[]) => void;
  addCard: (columnId: Status, title: string) => void;
  editCard: (id: number, title: string, status: Status) => void;
  handleDragOver: (event: DragOverEvent) => void;
  handleDragEnd: (event: DragEndEvent) => void;
  currentColumnId: Status;
  setCurrentColumnId: (id: Status) => void;
  currentCard: Task | NewTask;
  setCurrentCard: (task: Task | NewTask) => void;
  dialogOpen: boolean;
  setDialogOpen: (open: boolean) => void;
  dialogMode: DialogMode;
  setDialogMode: (mode: DialogMode) => void;
  initializeColumns: (tasks: Tasks) => void;
};

export const useTodoBoard = create<BoardState>((set, get) => {
  const findColumn = (id: string | null) => {
    const state = get();
    if (!id) return null;
    if (state.columns.some((column) => column.id === id)) {
      return state.columns.find((column) => column.id === id) ?? null;
    }
    const itemWithColumnId = state.columns.flatMap((column) => {
      const columnId = column.id;
      return column.tasks.map((task) => ({ itemId: task.id, columnId: columnId }));
    });
    const columnId = itemWithColumnId.find((item) => item.itemId.toString() === id)?.columnId;
    return state.columns.find((column) => column.id === columnId) ?? null;
  };

  const updateCardPosition = (columnId: string, fromIndex: number, toIndex: number) => {
    set((state) => {
      const column = state.columns.find((column) => column.id === columnId);
      if (!column) return state;
      const updatedCards = arrayMove(column.tasks, fromIndex, toIndex);
      const card = updatedCards[toIndex];
      patchTask({ ...card, status: column.id }, toIndex);
      return {
        columns: state.columns.map((column) =>
          column.id === columnId ? { ...column, tasks: updatedCards } : column
        ),
      };
    });
  };

  const addCard = async (columnId: Status, title: string) => {
    const newTask = await postTask({
      title: title,
      status: columnId,
    });
    set((state) => {
      const column = state.columns.find((column) => column.id === columnId);
      if (!column) return state;
      return {
        columns: state.columns.map((column) =>
          column.id === columnId
            ? {
                ...column,
                tasks: [...column.tasks, { ...newTask }],
              }
            : column
        ),
      };
    });
  };

  const editCard = async (id: number, title: string, status: Status) => {
    await patchTask({ id: id, title: title, status: status });
    set((state) => {
      return {
        columns: state.columns.map((column) => {
          return {
            ...column,
            tasks: column.tasks.map((task) => (task.id === id ? { ...task, title: title } : task)),
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
      const activeItems = activeColumn.tasks;
      const overItems = overColumn.tasks;
      const activeIndex = activeItems.findIndex((i) => i.id.toString() === activeId);
      const overIndex = overItems.findIndex((i) => i.id.toString() === overId);
      const newIndex = () => {
        const putOnBelowLastItem = overIndex === overItems.length - 1 && delta.y > 0;
        const modifier = putOnBelowLastItem ? 1 : 0;
        return overIndex >= 0 ? overIndex + modifier : overItems.length + 1;
      };
      return {
        columns: state.columns.map((column) => {
          if (column.id === activeColumn.id) {
            column.tasks = activeItems.filter((item) => item.id.toString() !== activeId);
            return column;
          }
          if (column.id === overColumn.id) {
            column.tasks = [
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
    const activeIndex = activeColumn.tasks.findIndex((task) => task.id.toString() === activeId);
    const overIndex = overColumn.tasks.findIndex((task) => task.id.toString() === overId);
    updateCardPosition(activeColumn.id, activeIndex, overIndex);
  };

  // TODO >> APIからはフラットで返して、ここで整形する
  const initializeColumns = (tasks: Tasks) => {
    set((state) => {
      return {
        columns: [
          {
            id: "todo",
            title: "ToDo",
            tasks: tasks.todo,
            showAddTask: true,
            showEditTask: true,
          },
          {
            id: "doing",
            title: "Doing",
            tasks: tasks.doing,
            showAddTask: true,
            showEditTask: true,
          },
          {
            id: "done",
            title: "Done",
            tasks: tasks.done,
            showAddTask: true,
            showEditTask: true,
          },
        ],
      };
    });
  };

  return {
    columns: [],
    setColumns: (columns) => set({ columns }),
    addCard,
    editCard,
    handleDragOver,
    handleDragEnd,
    currentColumnId: "todo",
    setCurrentColumnId: (id) => set({ currentColumnId: id }),
    currentCard: { title: "", status: "todo" },
    setCurrentCard: (task) => set({ currentCard: task }),
    dialogOpen: false,
    setDialogOpen: (open) => set({ dialogOpen: open }),
    dialogMode: "add",
    setDialogMode: (mode) => set({ dialogMode: mode }),
    initializeColumns,
  };
});
