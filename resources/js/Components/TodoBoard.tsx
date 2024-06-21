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
import { FC } from "react";
import { useTodoBoard } from "./useTodoBoard";

const TodoBoard: FC = () => {
  const columns = useTodoBoard((state) => state.columns);
  const handleDragEnd = useTodoBoard((state) => state.handleDragEnd);
  const handleDragOver = useTodoBoard((state) => state.handleDragOver);

  const sensors = useSensors(
    useSensor(PointerSensor),
    useSensor(KeyboardSensor, {
      coordinateGetter: sortableKeyboardCoordinates,
    })
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
    </DndContext>
  );
};

export default TodoBoard;
