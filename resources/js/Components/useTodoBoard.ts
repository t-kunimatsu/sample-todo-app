import { Task, Status, postTask, NewTask, patchTask, deleteTask } from "@/Apis/tasks";
import { DragEndEvent, DragOverEvent } from "@dnd-kit/core";
import { arrayMove } from "@dnd-kit/sortable";
import { create } from "zustand";
import { ColumnProps } from "./Column";
import { DialogMode, FormValues } from "./CardDialog";
import { UseFormReset } from "react-hook-form";

type BoardState = {
  columns: ColumnProps[];
  setColumns: (columns: ColumnProps[]) => void;
  addCard: (columnId: Status, title: string) => void;
  editCard: (id: number, title: string, status: Status) => void;
  deleteCard: (id: number) => void;
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
  resetCardDialogForm: UseFormReset<FormValues> | undefined;
  setResetCardDialogForm: (reset: UseFormReset<FormValues>) => void;
  initializeColumns: (tasks: Task[]) => void;
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

  const updateCardPosition = async (columnId: string, fromIndex: number, toIndex: number) => {
    const state = get();
    const column = state.columns.find((column) => column.id === columnId);
    if (!column) return state;
    const updatedCards = arrayMove(column.tasks, fromIndex, toIndex);
    const card = updatedCards[toIndex];
    const result = await patchTask({ ...card, status: column.id }, toIndex);
    if (result.status !== 200) {
      // TODO >> トースト表示する
      // すでにカラムを移動している場合は戻すの大変なので、並び順もフロントには反映してしまう
      // TODO >> 自動リカバリの仕組みが必要
    }
    set((state) => ({
      columns: state.columns.map((column) =>
        column.id === columnId ? { ...column, tasks: updatedCards } : column
      ),
    }));
  };

  const addCard = async (columnId: Status, title: string) => {
    const result = await postTask({
      title: title,
      status: columnId,
    });
    if (!result.task) {
      // TODO >> トースト表示する
      return;
    }
    const newTask = result.task;
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
    const result = await patchTask({ id: id, title: title, status: status });
    console.log(">>>>" + JSON.stringify(result, null, 2));
    if (result.status !== 200) {
      // TODO >> トースト表示する
      // TODO >> フロントには反映してしまう？（自動リカバリできるようにしたい）
      return;
    }
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

  const deleteCard = async (id: number) => {
    const result = await deleteTask(id);
    if (result.status !== 200 && result.status !== 404) {
      // TODO >> トースト表示する
      return;
    }
    set((state) => {
      return {
        columns: state.columns.map((column) => {
          return {
            ...column,
            tasks: column.tasks.filter((task) => task.id !== id),
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

  const initializeColumns = (tasks: Task[]) => {
    set((state) => {
      return {
        columns: [
          {
            id: "todo",
            title: "ToDo",
            tasks: tasks.filter((task) => task.status === "todo"),
            showAddTask: true,
            showEditTask: true,
          },
          {
            id: "doing",
            title: "Doing",
            tasks: tasks.filter((task) => task.status === "doing"),
            showAddTask: true,
            showEditTask: true,
          },
          {
            id: "done",
            title: "Done",
            tasks: tasks.filter((task) => task.status === "done"),
            showAddTask: false,
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
    deleteCard,
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
    resetCardDialogForm: undefined,
    setResetCardDialogForm: (reset) => set({ resetCardDialogForm: reset }),
    initializeColumns,
  };
});
