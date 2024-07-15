import Column from "@/Components/Column";
import {
  closestCorners,
  DndContext,
  KeyboardSensor,
  PointerSensor,
  useSensor,
  useSensors,
} from "@dnd-kit/core";
import { sortableKeyboardCoordinates } from "@dnd-kit/sortable";
import { Box } from "@mui/material";
import { useTodoBoard } from "./useTodoBoard";
import CardDialog from "./CardDialog";
import { useCallback } from "react";
import { useShallow } from "zustand/react/shallow";
import { Task } from "@/Apis/tasks";

const TodoBoard: React.FC = () => {
  const {
    columns,
    handleDragEnd,
    handleDragOver,
    addCard,
    editCard,
    currentColumnId,
    currentCard,
    dialogOpen,
    setDialogOpen,
    dialogMode,
  } = useTodoBoard(
    useShallow((state) => ({
      columns: state.columns,
      handleDragEnd: state.handleDragEnd,
      handleDragOver: state.handleDragOver,
      addCard: state.addCard,
      editCard: state.editCard,
      currentColumnId: state.currentColumnId,
      currentCard: state.currentCard,
      dialogOpen: state.dialogOpen,
      setDialogOpen: state.setDialogOpen,
      dialogMode: state.dialogMode,
    }))
  );

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
  );

  const saveCard = useCallback(
    (title: string) => {
      if (dialogMode === "add") {
        addCard(currentColumnId, title);
        return;
      }
      editCard((currentCard as Task).id, title, currentColumnId);
    },
    [dialogMode, currentColumnId, currentCard, addCard, editCard]
  );

  const handleSaveCard = useCallback(
    (title: string) => {
      saveCard(title);
      setDialogOpen(false);
    },
    [saveCard, setDialogOpen]
  );

  return (
    <DndContext
      sensors={sensors}
      collisionDetection={closestCorners}
      onDragEnd={handleDragEnd}
      onDragOver={handleDragOver}
    >
      <Box sx={{ display: "flex", flexDirection: "row", padding: "20px" }}>
        {columns.map((column) => (
          <Column key={column.id} {...column} />
        ))}
      </Box>
      <CardDialog open={dialogOpen} onSave={handleSaveCard} />
    </DndContext>
  );
};

export default TodoBoard;
